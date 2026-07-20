<?php

namespace App\Providers;

use App\Contracts\Erp\ErpCatalogConnector;
use App\Erp\Connectors\EstoqManErpConnector;
use App\Erp\Connectors\NullErpConnector;
use App\Erp\Parsers\EstoqManFileParser;
use App\Erp\Services\CatalogSyncService;
use App\Erp\Services\ErpCatalogSyncRunner;
use Illuminate\Support\ServiceProvider;

class ErpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ErpCatalogConnector::class, function ($app) {
            $driver = config('erp.connector', 'null');

            return match ($driver) {
                'null' => new NullErpConnector,
                'estoqman' => new EstoqManErpConnector(
                    parser: $app->make(EstoqManFileParser::class),
                    productsFile: config('erp.connectors.estoqman.products_file'),
                ),
                default => throw new \InvalidArgumentException("Conector ERP desconhecido: {$driver}"),
            };
        });

        $this->app->singleton(CatalogSyncService::class, function ($app) {
            return new CatalogSyncService(
                connector: $app->make(ErpCatalogConnector::class),
                erpSource: config('erp.source', 'erp'),
                deactivateMissing: (bool) config('erp.sync.deactivate_missing', true),
                fallbackCategorySlug: config('erp.sync.fallback_category_slug', 'sem-categoria'),
            );
        });

        $this->app->singleton(ErpCatalogSyncRunner::class);
    }
}