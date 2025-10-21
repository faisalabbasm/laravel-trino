<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trino Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to Trino (formerly PrestoSQL).
    | Trino acts as a distributed SQL query engine that can query data
    | from multiple sources including MySQL, MongoDB, SQL Server, etc.
    |
    */

    'host' => env('TRINO_HOST', 'localhost'),
    'port' => env('TRINO_PORT', 8080),
    'catalog' => env('TRINO_CATALOG', 'mysql'),
    'schema' => env('TRINO_SCHEMA', 'default'),
    'user' => env('TRINO_USER', 'trino'),
    
    /*
    |--------------------------------------------------------------------------
    | Connection Timeout
    |--------------------------------------------------------------------------
    |
    | Request timeout in seconds for Trino HTTP requests
    |
    */
    'timeout' => env('TRINO_TIMEOUT', 30),
    
    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, logs all queries and responses from Trino
    |
    */
    'debug' => env('TRINO_DEBUG', false),
    
    /*
    |--------------------------------------------------------------------------
    | Query Result Caching
    |--------------------------------------------------------------------------
    |
    | Enable caching of query results to speed up repeated queries.
    | Cache TTL is in seconds (default: 300 = 5 minutes)
    |
    */
    'enable_cache' => env('TRINO_ENABLE_CACHE', true),
    'cache_ttl' => env('TRINO_CACHE_TTL', 300), // 5 minutes

];

