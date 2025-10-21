<?php

namespace App\Http\Controllers;

use App\Services\TrinoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrinoController extends Controller
{
    protected TrinoService $trinoService;

    public function __construct(TrinoService $trinoService)
    {
        $this->trinoService = $trinoService;
    }

    /**
     * Test Trino connection
     */
    public function test(): JsonResponse
    {
        try {
            $result = $this->trinoService->testConnection();
            
            // Also get some metadata
            $catalogs = $this->trinoService->listCatalogs();
            $schemas = $this->trinoService->listSchemas();
            $tables = $this->trinoService->listTables();

            return response()->json([
                'connection' => $result,
                'catalogs' => $catalogs,
                'schemas' => $schemas,
                'tables' => $tables,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute a custom Trino query
     */
    public function executeQuery(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string',
            'catalog' => 'nullable|string',
            'schema' => 'nullable|string',
        ]);

        try {
            $result = $this->trinoService->query(
                $request->input('query'),
                $request->input('catalog'),
                $request->input('schema')
            );

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get users from MySQL via Trino
     */
    public function getUsers(Request $request): JsonResponse
    {
        try {
            $limit = (int) $request->input('limit', 10);
            $offset = (int) $request->input('offset', 0);

            $query = "SELECT * FROM mysql.test_db.users LIMIT {$limit}";
            
            if ($offset > 0) {
                $query .= " OFFSET {$offset}";
            }
            
            \Log::info("Executing Trino query: " . $query);
            $result = $this->trinoService->query($query);

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            \Log::error("Trino query error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get products from MySQL via Trino
     */
    public function getProducts(Request $request): JsonResponse
    {
        try {
            $limit = (int) $request->input('limit', 10);
            $offset = (int) $request->input('offset', 0);

            $query = "SELECT * FROM mysql.test_db.products LIMIT {$limit}";
            
            if ($offset > 0) {
                $query .= " OFFSET {$offset}";
            }
            
            \Log::info("Executing Trino query: " . $query);
            $result = $this->trinoService->query($query);

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            \Log::error("Trino query error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

