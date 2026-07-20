<?php

namespace Tests\Unit;

use App\Data\Erp\CatalogSyncResult;
use App\Filament\Pages\SyncCatalog;
use Tests\TestCase;

class SyncCatalogSummaryTest extends TestCase
{
    public function test_format_sync_summary_separa_categorias_e_produtos(): void
    {
        $page = new SyncCatalog;
        $method = (new \ReflectionClass($page))->getMethod('formatSyncSummary');
        $method->setAccessible(true);

        $summary = $method->invoke($page, new CatalogSyncResult(
            categoriesCreated: 2,
            categoriesUpdated: 8,
            productsCreated: 729,
            productsUpdated: 8625,
            productsDeactivated: 15,
            productsProcessed: 8625,
            productsUnique: 8625,
        ));

        $this->assertStringContainsString('<strong>Categorias</strong>: 2 criadas, 8 atualizadas', $summary);
        $this->assertStringContainsString('<strong>Produtos</strong>: 729 criados, 8.625 atualizados, 15 desativados', $summary);
        $this->assertStringContainsString('<strong>Arquivo</strong>: 8.625 registros', $summary);
    }
}