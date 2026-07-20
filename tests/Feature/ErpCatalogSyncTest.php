<?php

namespace Tests\Feature;

use App\Contracts\Erp\ErpCatalogConnector;
use App\Data\Erp\ErpCategoryData;
use App\Data\Erp\ErpProductData;
use App\Erp\Services\CatalogSyncService;
use App\Models\Category;
use App\Models\ErpSyncLog;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErpCatalogSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_cria_e_atualiza_produtos_do_erp(): void
    {
        $connector = new class implements ErpCatalogConnector
        {
            private int $calls = 0;

            public function fetchCategories(): array
            {
                return [
                    new ErpCategoryData(externalId: 'cat-1', nome: 'Bebidas', ordem: 1),
                ];
            }

            public function fetchProducts(): array
            {
                $this->calls++;

                if ($this->calls === 1) {
                    return [
                        new ErpProductData(
                            externalId: 'prod-1',
                            nome: 'Refrigerante 2L',
                            categoryExternalId: 'cat-1',
                            codigo: 'REF-001',
                            barcode: '7891234567890',
                            unidade: 'Fardo 6un',
                            marca: 'Cola',
                            preco: 38.90,
                        ),
                    ];
                }

                return [
                    new ErpProductData(
                        externalId: 'prod-1',
                        nome: 'Refrigerante 2L Atualizado',
                        categoryExternalId: 'cat-1',
                        codigo: 'REF-001',
                        preco: 42.50,
                    ),
                    new ErpProductData(
                        externalId: 'prod-2',
                        nome: 'Água Mineral',
                        categoryExternalId: 'cat-1',
                        codigo: 'AGU-001',
                    ),
                ];
            }
        };

        $service = new CatalogSyncService($connector, 'test-erp');

        $first = $service->sync();
        $this->assertSame(1, $first->categoriesCreated);
        $this->assertSame(1, $first->productsCreated);

        $product = Product::query()->where('erp_external_id', 'prod-1')->first();
        $this->assertSame('Refrigerante 2L', $product->nome);
        $this->assertSame('38.90', $product->preco);
        $this->assertSame('7891234567890', $product->barcode);
        $this->assertTrue($product->isFromErp());

        $second = $service->sync();
        $this->assertSame(1, $second->productsUpdated);
        $this->assertSame(1, $second->productsCreated);

        $product->refresh();
        $this->assertSame('Refrigerante 2L Atualizado', $product->nome);
        $this->assertSame('42.50', $product->preco);

        $this->assertDatabaseHas('erp_sync_logs', ['erp_source' => 'test-erp', 'status' => ErpSyncLog::STATUS_SUCCESS]);
    }

    public function test_sync_desativa_produtos_ausentes_no_erp(): void
    {
        $connector = new class implements ErpCatalogConnector
        {
            private int $calls = 0;

            public function fetchCategories(): array
            {
                return [new ErpCategoryData(externalId: 'cat-1', nome: 'Geral')];
            }

            public function fetchProducts(): array
            {
                $this->calls++;

                return $this->calls === 1
                    ? [
                        new ErpProductData(externalId: 'prod-1', nome: 'Ativo', categoryExternalId: 'cat-1'),
                        new ErpProductData(externalId: 'prod-2', nome: 'Será removido', categoryExternalId: 'cat-1'),
                    ]
                    : [
                        new ErpProductData(externalId: 'prod-1', nome: 'Ativo', categoryExternalId: 'cat-1'),
                    ];
            }
        };

        $service = new CatalogSyncService($connector, 'test-erp', deactivateMissing: true);

        $service->sync();
        $result = $service->sync();

        $this->assertSame(1, $result->productsDeactivated);
        $this->assertTrue(Product::query()->where('erp_external_id', 'prod-1')->value('ativo'));
        $this->assertFalse(Product::query()->where('erp_external_id', 'prod-2')->value('ativo'));
    }

    public function test_comando_artisan_executa_sync(): void
    {
        $this->artisan('erp:sync-catalog')->assertSuccessful();
    }
}