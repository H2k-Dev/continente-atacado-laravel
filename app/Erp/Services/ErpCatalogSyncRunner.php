<?php

namespace App\Erp\Services;

use App\Data\Erp\CatalogSyncResult;
use App\Erp\Connectors\EstoqManErpConnector;
use App\Erp\Exceptions\ErpSyncException;
use App\Erp\Parsers\EstoqManFileParser;
use App\Support\ErpCatalogFileStorage;
use Illuminate\Support\Facades\File;

class ErpCatalogSyncRunner
{
    public function __construct(
        protected EstoqManFileParser $parser,
    ) {}

    public function syncStoredFile(): CatalogSyncResult
    {
        $path = ErpCatalogFileStorage::productsFilePath();

        if (! File::exists($path)) {
            throw new ErpSyncException('Nenhum arquivo de catálogo importado. Envie o cargapro.txt antes de sincronizar.');
        }

        return $this->syncFile($path);
    }

    public function syncFile(string $path): CatalogSyncResult
    {
        $connector = new EstoqManErpConnector($this->parser, $path);

        return (new CatalogSyncService(
            connector: $connector,
            erpSource: config('erp.source', 'estoqman'),
            deactivateMissing: (bool) config('erp.sync.deactivate_missing', true),
            fallbackCategorySlug: config('erp.sync.fallback_category_slug', 'sem-categoria'),
        ))->sync();
    }
}