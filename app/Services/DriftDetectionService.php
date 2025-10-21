<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DriftDetectionService
{
    protected TrinoService $trino;

    public function __construct(TrinoService $trino)
    {
        $this->trino = $trino;
    }

    /**
     * Detect schema drift between two databases
     *
     * @param string $sourceDb Source database catalog (e.g., 'mysql_prod')
     * @param string $targetDb Target database catalog (e.g., 'mysql_staging')
     * @param string $schema Schema name (e.g., 'test_db')
     * @return array Detected drifts
     */
    public function detectSchemaDrift(string $sourceDb, string $targetDb, string $schema): array
    {
        $query = "
            WITH source_cols AS (
                SELECT 
                    table_name,
                    column_name,
                    data_type,
                    is_nullable
                FROM {$sourceDb}.information_schema.columns
                WHERE table_schema = '{$schema}'
            ),
            target_cols AS (
                SELECT 
                    table_name,
                    column_name,
                    data_type,
                    is_nullable
                FROM {$targetDb}.information_schema.columns
                WHERE table_schema = '{$schema}'
            )
            SELECT 
                COALESCE(s.table_name, t.table_name) as table_name,
                COALESCE(s.column_name, t.column_name) as column_name,
                s.data_type as source_type,
                t.data_type as target_type,
                s.is_nullable as source_nullable,
                t.is_nullable as target_nullable,
                CASE 
                    WHEN s.column_name IS NULL THEN 'Missing in Source'
                    WHEN t.column_name IS NULL THEN 'Missing in Target'
                    WHEN s.data_type != t.data_type THEN 'Type Mismatch'
                    WHEN s.is_nullable != t.is_nullable THEN 'Nullability Mismatch'
                END as drift_type
            FROM source_cols s
            FULL OUTER JOIN target_cols t 
                ON s.table_name = t.table_name 
                AND s.column_name = t.column_name
            WHERE s.column_name IS NULL 
               OR t.column_name IS NULL
               OR s.data_type != t.data_type
               OR s.is_nullable != t.is_nullable
            ORDER BY table_name, column_name
        ";

        Log::info('Detecting schema drift', [
            'source' => $sourceDb,
            'target' => $targetDb,
            'schema' => $schema,
        ]);

        return $this->trino->query($query);
    }

    /**
     * Compare table row counts between databases
     *
     * @param string $sourceDb Source database
     * @param string $targetDb Target database
     * @param string $schema Schema name
     * @return array Tables with row count differences
     */
    public function detectRowCountDrift(string $sourceDb, string $targetDb, string $schema): array
    {
        // First, get list of tables in source
        $tablesQuery = "
            SELECT DISTINCT table_name 
            FROM {$sourceDb}.information_schema.tables
            WHERE table_schema = '{$schema}'
            AND table_type = 'BASE TABLE'
        ";
        
        $tablesResult = $this->trino->query($tablesQuery);
        $drifts = [];

        foreach ($tablesResult['data'] as $tableRow) {
            $tableName = $tableRow['table_name'];
            
            try {
                $countQuery = "
                    SELECT 
                        '{$tableName}' as table_name,
                        (SELECT COUNT(*) FROM {$sourceDb}.{$schema}.{$tableName}) as source_count,
                        (SELECT COUNT(*) FROM {$targetDb}.{$schema}.{$tableName}) as target_count
                ";

                $result = $this->trino->query($countQuery);
                
                if (!empty($result['data'])) {
                    $row = $result['data'][0];
                    $difference = $row['source_count'] - $row['target_count'];
                    
                    if ($difference != 0) {
                        $drifts[] = [
                            'table_name' => $tableName,
                            'source_count' => $row['source_count'],
                            'target_count' => $row['target_count'],
                            'difference' => $difference,
                            'drift_percentage' => $row['source_count'] > 0 
                                ? round(($difference / $row['source_count']) * 100, 2) 
                                : 0,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Could not compare table {$tableName}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'columns' => [
                ['name' => 'table_name'],
                ['name' => 'source_count'],
                ['name' => 'target_count'],
                ['name' => 'difference'],
                ['name' => 'drift_percentage'],
            ],
            'data' => $drifts,
            'rowCount' => count($drifts),
        ];
    }

    /**
     * Detect missing tables between databases
     *
     * @param string $sourceDb Source database
     * @param string $targetDb Target database
     * @param string $schema Schema name
     * @return array Missing tables
     */
    public function detectMissingTables(string $sourceDb, string $targetDb, string $schema): array
    {
        $query = "
            -- Tables in source but not in target
            SELECT 
                table_name,
                'Missing in Target' as drift_type
            FROM {$sourceDb}.information_schema.tables
            WHERE table_schema = '{$schema}'
            AND table_type = 'BASE TABLE'
            EXCEPT
            SELECT 
                table_name,
                'Missing in Target' as drift_type
            FROM {$targetDb}.information_schema.tables
            WHERE table_schema = '{$schema}'
            AND table_type = 'BASE TABLE'
            
            UNION ALL
            
            -- Tables in target but not in source
            SELECT 
                table_name,
                'Extra in Target' as drift_type
            FROM {$targetDb}.information_schema.tables
            WHERE table_schema = '{$schema}'
            AND table_type = 'BASE TABLE'
            EXCEPT
            SELECT 
                table_name,
                'Extra in Target' as drift_type
            FROM {$sourceDb}.information_schema.tables
            WHERE table_schema = '{$schema}'
            AND table_type = 'BASE TABLE'
        ";

        return $this->trino->query($query);
    }

    /**
     * Capture current schema snapshot
     *
     * @param string $database Database catalog
     * @param string $schema Schema name
     * @return string Snapshot ID
     */
    public function captureSnapshot(string $database, string $schema): string
    {
        $snapshotId = 'SNAP_' . now()->format('YmdHis');
        
        // Get current schema state
        $query = "
            SELECT 
                table_name,
                column_name,
                data_type,
                is_nullable,
                column_default
            FROM {$database}.information_schema.columns
            WHERE table_schema = '{$schema}'
            ORDER BY table_name, ordinal_position
        ";

        $result = $this->trino->query($query);
        
        // Store snapshot in database
        \DB::table('schema_snapshots')->insert([
            'snapshot_id' => $snapshotId,
            'database' => $database,
            'schema' => $schema,
            'snapshot_data' => json_encode($result),
            'columns_captured' => $result['rowCount'] ?? 0,
            'captured_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('Schema snapshot captured', [
            'snapshot_id' => $snapshotId,
            'database' => $database,
            'schema' => $schema,
            'columns_captured' => $result['rowCount'] ?? 0,
        ]);

        return $snapshotId;
    }

    /**
     * Compare current state with a previous snapshot
     *
     * @param string $snapshotId Snapshot identifier
     * @param string $database Current database
     * @param string $schema Current schema
     * @return array Detected drifts
     */
    public function compareWithSnapshot(string $snapshotId, string $database, string $schema): array
    {
        // Retrieve snapshot from database
        $snapshotRecord = \DB::table('schema_snapshots')
            ->where('snapshot_id', $snapshotId)
            ->first();
        
        if (!$snapshotRecord) {
            throw new \Exception("Snapshot not found: {$snapshotId}");
        }

        $snapshot = [
            'snapshot_id' => $snapshotRecord->snapshot_id,
            'captured_at' => $snapshotRecord->captured_at,
            'database' => $snapshotRecord->database,
            'schema' => $snapshotRecord->schema,
            'data' => json_decode($snapshotRecord->snapshot_data, true),
        ];

        // Get current state
        $query = "
            SELECT 
                table_name,
                column_name,
                data_type,
                is_nullable
            FROM {$database}.information_schema.columns
            WHERE table_schema = '{$schema}'
        ";

        $currentState = $this->trino->query($query);
        
        // Build lookup for comparison
        $currentColumns = [];
        foreach ($currentState['data'] as $col) {
            $key = $col['table_name'] . '.' . $col['column_name'];
            $currentColumns[$key] = $col;
        }
        
        $snapshotColumns = [];
        foreach ($snapshot['data']['data'] as $col) {
            $key = $col['table_name'] . '.' . $col['column_name'];
            $snapshotColumns[$key] = $col;
        }
        
        // Find drifts
        $drifts = [];
        
        // Check for removed or changed columns
        foreach ($snapshotColumns as $key => $snapshotCol) {
            if (!isset($currentColumns[$key])) {
                $drifts[] = [
                    'table_name' => $snapshotCol['table_name'],
                    'column_name' => $snapshotCol['column_name'],
                    'drift_type' => 'Column Removed',
                    'previous_type' => $snapshotCol['data_type'],
                    'current_type' => null,
                    'snapshot_date' => $snapshot['captured_at'],
                ];
            } elseif ($currentColumns[$key]['data_type'] !== $snapshotCol['data_type']) {
                $drifts[] = [
                    'table_name' => $snapshotCol['table_name'],
                    'column_name' => $snapshotCol['column_name'],
                    'drift_type' => 'Type Changed',
                    'previous_type' => $snapshotCol['data_type'],
                    'current_type' => $currentColumns[$key]['data_type'],
                    'snapshot_date' => $snapshot['captured_at'],
                ];
            } elseif ($currentColumns[$key]['is_nullable'] !== $snapshotCol['is_nullable']) {
                $drifts[] = [
                    'table_name' => $snapshotCol['table_name'],
                    'column_name' => $snapshotCol['column_name'],
                    'drift_type' => 'Nullability Changed',
                    'previous_nullable' => $snapshotCol['is_nullable'],
                    'current_nullable' => $currentColumns[$key]['is_nullable'],
                    'snapshot_date' => $snapshot['captured_at'],
                ];
            }
        }
        
        // Check for new columns
        foreach ($currentColumns as $key => $currentCol) {
            if (!isset($snapshotColumns[$key])) {
                $drifts[] = [
                    'table_name' => $currentCol['table_name'],
                    'column_name' => $currentCol['column_name'],
                    'drift_type' => 'Column Added',
                    'previous_type' => null,
                    'current_type' => $currentCol['data_type'],
                    'snapshot_date' => $snapshot['captured_at'],
                ];
            }
        }

        Log::info('Compared with snapshot', [
            'snapshot_id' => $snapshotId,
            'drifts_found' => count($drifts),
        ]);

        return $drifts;
    }

    /**
     * Get comprehensive drift report
     *
     * @param string $sourceDb Source database
     * @param string $targetDb Target database
     * @param string $schema Schema name
     * @return array Complete drift report
     */
    public function getDriftReport(string $sourceDb, string $targetDb, string $schema): array
    {
        $report = [
            'timestamp' => now()->toIso8601String(),
            'source' => $sourceDb,
            'target' => $targetDb,
            'schema' => $schema,
            'missing_tables' => [],
            'schema_drifts' => [],
            'data_drifts' => [],
            'summary' => [
                'total_drifts' => 0,
                'critical_drifts' => 0,
                'tables_missing' => 0,
                'columns_different' => 0,
                'row_count_drifts' => 0,
            ],
        ];

        try {
            // Detect missing tables
            $missingTables = $this->detectMissingTables($sourceDb, $targetDb, $schema);
            $report['missing_tables'] = $missingTables['data'] ?? [];
            $report['summary']['tables_missing'] = count($report['missing_tables']);

            // Detect schema drifts
            $schemaDrifts = $this->detectSchemaDrift($sourceDb, $targetDb, $schema);
            $report['schema_drifts'] = $schemaDrifts['data'] ?? [];
            $report['summary']['columns_different'] = count($report['schema_drifts']);
            
            // Detect row count drifts
            $dataDrifts = $this->detectRowCountDrift($sourceDb, $targetDb, $schema);
            $report['data_drifts'] = $dataDrifts['data'] ?? [];
            $report['summary']['row_count_drifts'] = count($report['data_drifts']);
            
            // Calculate total and critical drifts
            $report['summary']['total_drifts'] = 
                $report['summary']['tables_missing'] +
                $report['summary']['columns_different'] +
                $report['summary']['row_count_drifts'];
            
            // Critical drifts: type mismatches and missing columns
            $criticalTypes = ['Type Mismatch', 'Missing in Target', 'Missing in Source'];
            $report['summary']['critical_drifts'] = collect($report['schema_drifts'])
                ->whereIn('drift_type', $criticalTypes)
                ->count();

        } catch (\Exception $e) {
            Log::error('Error generating drift report', [
                'error' => $e->getMessage(),
                'source' => $sourceDb,
                'target' => $targetDb,
            ]);
            
            $report['error'] = $e->getMessage();
        }

        return $report;
    }
}

