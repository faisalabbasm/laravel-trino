<?php

namespace App\Http\Controllers;

use App\Services\DriftDetectionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DriftDetectionController extends Controller
{
    protected DriftDetectionService $driftService;

    public function __construct(DriftDetectionService $driftService)
    {
        $this->driftService = $driftService;
    }

    /**
     * Detect schema drift between two databases
     */
    public function detectSchemaDrift(Request $request): JsonResponse
    {
        $request->validate([
            'source_db' => 'required|string',
            'target_db' => 'required|string',
            'schema' => 'required|string',
        ]);

        try {
            $result = $this->driftService->detectSchemaDrift(
                $request->source_db,
                $request->target_db,
                $request->schema
            );

            return response()->json([
                'success' => true,
                'drifts' => $result['data'] ?? [],
                'count' => $result['rowCount'] ?? 0,
                'message' => 'Schema drift detection completed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detect row count differences between databases
     */
    public function detectRowCountDrift(Request $request): JsonResponse
    {
        $request->validate([
            'source_db' => 'required|string',
            'target_db' => 'required|string',
            'schema' => 'required|string',
        ]);

        try {
            $result = $this->driftService->detectRowCountDrift(
                $request->source_db,
                $request->target_db,
                $request->schema
            );

            return response()->json([
                'success' => true,
                'drifts' => $result['data'] ?? [],
                'count' => $result['rowCount'] ?? 0,
                'message' => 'Row count drift detection completed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detect missing tables between databases
     */
    public function detectMissingTables(Request $request): JsonResponse
    {
        $request->validate([
            'source_db' => 'required|string',
            'target_db' => 'required|string',
            'schema' => 'required|string',
        ]);

        try {
            $result = $this->driftService->detectMissingTables(
                $request->source_db,
                $request->target_db,
                $request->schema
            );

            return response()->json([
                'success' => true,
                'missing_tables' => $result['data'] ?? [],
                'count' => $result['rowCount'] ?? 0,
                'message' => 'Missing tables detection completed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get comprehensive drift report
     */
    public function getDriftReport(Request $request): JsonResponse
    {
        $request->validate([
            'source_db' => 'required|string',
            'target_db' => 'required|string',
            'schema' => 'required|string',
        ]);

        try {
            $report = $this->driftService->getDriftReport(
                $request->source_db,
                $request->target_db,
                $request->schema
            );

            return response()->json([
                'success' => true,
                'report' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Capture schema snapshot
     */
    public function captureSnapshot(Request $request): JsonResponse
    {
        $request->validate([
            'database' => 'required|string',
            'schema' => 'required|string',
        ]);

        try {
            $snapshotId = $this->driftService->captureSnapshot(
                $request->database,
                $request->schema
            );

            return response()->json([
                'success' => true,
                'snapshot_id' => $snapshotId,
                'message' => 'Snapshot captured successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Compare current state with previous snapshot
     */
    public function compareWithSnapshot(Request $request, string $snapshotId): JsonResponse
    {
        $request->validate([
            'database' => 'required|string',
            'schema' => 'required|string',
        ]);

        try {
            $drifts = $this->driftService->compareWithSnapshot(
                $snapshotId,
                $request->database,
                $request->schema
            );

            return response()->json([
                'success' => true,
                'snapshot_id' => $snapshotId,
                'drifts' => $drifts,
                'count' => count($drifts),
                'message' => 'Snapshot comparison completed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all snapshots
     */
    public function listSnapshots(Request $request): JsonResponse
    {
        try {
            $database = $request->input('database');
            $schema = $request->input('schema');

            $query = \DB::table('schema_snapshots')
                ->select('snapshot_id', 'database', 'schema', 'columns_captured', 'captured_at')
                ->orderBy('captured_at', 'desc');

            if ($database) {
                $query->where('database', $database);
            }

            if ($schema) {
                $query->where('schema', $schema);
            }

            $snapshots = $query->limit(50)->get();

            return response()->json([
                'success' => true,
                'snapshots' => $snapshots,
                'count' => count($snapshots),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

