<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ERP connector
    |--------------------------------------------------------------------------
    |
    | Which connector implementation resolves ErpCatalogConnector.
    | Use "null" until a real ERP integration is configured.
    |
    */
    'connector' => env('ERP_CONNECTOR', 'null'),

    'source' => env('ERP_SOURCE', 'erp'),

    /*
    |--------------------------------------------------------------------------
    | Sync behaviour
    |--------------------------------------------------------------------------
    */
    'sync' => [
        // Deactivate local products that disappear from the ERP feed.
        'deactivate_missing' => env('ERP_SYNC_DEACTIVATE_MISSING', true),

        // Default category slug when a product has no category mapping.
        'fallback_category_slug' => env('ERP_SYNC_FALLBACK_CATEGORY', 'sem-categoria'),

        // Scheduled sync (cron expression). Null disables scheduling.
        'schedule' => env('ERP_SYNC_SCHEDULE', '0 */6 * * *'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Connector credentials (per-driver)
    |--------------------------------------------------------------------------
    */
    'connectors' => [
        'null' => [],

        // EstoqMan PDV / Schneider Sistemas — NT/PDV 8 (cargapro.txt)
        'estoqman' => [
            'products_file' => env('ERP_ESTOQMAN_PRODUCTS_FILE', storage_path('erp/cargapro.txt')),
        ],

        // Generic REST placeholder for other ERPs.
        'api' => [
            'base_url' => env('ERP_API_BASE_URL'),
            'token' => env('ERP_API_TOKEN'),
            'timeout' => (int) env('ERP_API_TIMEOUT', 30),
        ],
    ],

];