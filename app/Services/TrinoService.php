<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class TrinoService
{
    protected Client $client;
    protected string $baseUrl;
    protected string $catalog;
    protected string $schema;
    protected string $user;
    protected int $timeout;
    protected bool $debug;

    public function __construct()
    {
        $host = config('trino.host');
        $port = config('trino.port');
        $this->baseUrl = "http://{$host}:{$port}";
        $this->catalog = config('trino.catalog');
        $this->schema = config('trino.schema');
        $this->user = config('trino.user');
        $this->timeout = config('trino.timeout', 30);
        $this->debug = config('trino.debug', false);

        $this->client = new Client([
            'timeout' => $this->timeout,
            'headers' => [
                'X-Trino-User' => $this->user,
                'X-Trino-Catalog' => $this->catalog,
                'X-Trino-Schema' => $this->schema,
            ],
        ]);
    }

    /**
     * Execute a SQL query via Trino
     *
     * @param string $query The SQL query to execute
     * @param string|null $catalog Optional catalog override
     * @param string|null $schema Optional schema override
     * @return array The query results
     * @throws \Exception
     */
    public function query(string $query, ?string $catalog = null, ?string $schema = null): array
    {
        try {
            $headers = [
                'X-Trino-User' => $this->user,
                'X-Trino-Catalog' => $catalog ?? $this->catalog,
                'X-Trino-Schema' => $schema ?? $this->schema,
            ];

            if ($this->debug) {
                Log::info('Trino Query', [
                    'query' => $query,
                    'catalog' => $catalog ?? $this->catalog,
                    'schema' => $schema ?? $this->schema,
                ]);
            }

            // Submit the query
            $response = $this->client->post("{$this->baseUrl}/v1/statement", [
                'headers' => $headers,
                'body' => $query,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            

            // Poll for results if query is still running
            while (isset($result['nextUri']) && empty($result['data'])) {
                $response = $this->client->get($result['nextUri']);
                $result = json_decode($response->getBody()->getContents(), true);
                
                // Add a small delay to avoid overwhelming the server
                usleep(100000); // 100ms
            }

            // Continue fetching if there's more data
            while (isset($result['nextUri'])) {
                $nextResponse = $this->client->get($result['nextUri']);
                $nextResult = json_decode($nextResponse->getBody()->getContents(), true);
                
                if (isset($nextResult['data'])) {
                    if (!isset($result['data'])) {
                        $result['data'] = [];
                    }
                    $result['data'] = array_merge($result['data'], $nextResult['data']);
                }
                
                $result['nextUri'] = $nextResult['nextUri'] ?? null;
                
                if (!isset($result['nextUri'])) {
                    break;
                }
            }

            if ($this->debug) {
                Log::info('Trino Query Result', [
                    'rowCount' => isset($result['data']) ? count($result['data']) : 0,
                ]);

            }
            
            return $this->formatResult($result);

        } catch (GuzzleException $e) {
            Log::error('Trino Query Error', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception("Trino query failed: " . $e->getMessage());
        }
    }

    /**
     * Test the connection to Trino
     *
     * @return array
     */
    public function testConnection(): array
    {
        try {
            $result = $this->query("SELECT 1 as test");
            
            return [
                'success' => true,
                'message' => 'Successfully connected to Trino',
                'result' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to connect to Trino',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * List all catalogs available in Trino
     *
     * @return array
     */
    public function listCatalogs(): array
    {
        return $this->query("SHOW CATALOGS");
    }

    /**
     * List all schemas in a catalog
     *
     * @param string|null $catalog
     * @return array
     */
    public function listSchemas(?string $catalog = null): array
    {
        $catalogName = $catalog ?? $this->catalog;
        return $this->query("SHOW SCHEMAS FROM {$catalogName}");
    }

    /**
     * List all tables in a schema
     *
     * @param string|null $schema
     * @param string|null $catalog
     * @return array
     */
    public function listTables(?string $schema = null, ?string $catalog = null): array
    {
        $schemaName = $schema ?? $this->schema;
        $catalogName = $catalog ?? $this->catalog;
        return $this->query("SHOW TABLES FROM {$catalogName}.{$schemaName}");
    }

    /**
     * Describe a table structure
     *
     * @param string $table
     * @param string|null $schema
     * @param string|null $catalog
     * @return array
     */
    public function describeTable(string $table, ?string $schema = null, ?string $catalog = null): array
    {
        $schemaName = $schema ?? $this->schema;
        $catalogName = $catalog ?? $this->catalog;
        return $this->query("DESCRIBE {$catalogName}.{$schemaName}.{$table}");
    }

    /**
     * Format the Trino result into a more usable structure
     *
     * @param array $result
     * @return array
     */
    protected function formatResult(array $result): array
    {
        $columns = $result['columns'] ?? [];
        $data = $result['data'] ?? [];

        $formattedData = [];
        
        foreach ($data as $row) {
            $formattedRow = [];
            foreach ($columns as $index => $column) {
                $formattedRow[$column['name']] = $row[$index] ?? null;
            }
            $formattedData[] = $formattedRow;
        }

        return [
            'columns' => $columns,
            'data' => $formattedData,
            'rowCount' => count($formattedData),
            'stats' => $result['stats'] ?? [],
        ];
    }

    /**
     * Execute a query on a specific data source
     *
     * @param string $dataSource (mysql, mongodb, sqlserver, etc.)
     * @param string $query
     * @param string|null $schema
     * @return array
     */
    public function queryDataSource(string $dataSource, string $query, ?string $schema = null): array
    {
        return $this->query($query, $dataSource, $schema);
    }
}


