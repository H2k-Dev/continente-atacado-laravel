<?php

namespace Tests\Feature;

use App\Filament\Pages\SyncCatalog;
use App\Models\Product;
use App\Models\User;
use App\Support\ErpCatalogFileStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSyncCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected string $importedFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importedFile = storage_path('framework/testing-admin-cargapro.txt');

        config([
            'erp.connectors.estoqman.products_file' => $this->importedFile,
            'erp.source' => 'estoqman',
        ]);
    }

    protected function tearDown(): void
    {
        File::delete($this->importedFile);

        parent::tearDown();
    }

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_pagina_de_sincronizacao_carrega_no_admin(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get('/admin/sync-catalog')
            ->assertOk()
            ->assertSee('Sincronizar catálogo ERP');
    }

    public function test_admin_pode_importar_arquivo_e_sincronizar(): void
    {
        Storage::fake('local');

        $fixture = base_path('tests/fixtures/estoqman/cargapro.txt');
        $relativePath = 'erp-uploads/cargapro.txt';
        Storage::disk('local')->put($relativePath, File::get($fixture));

        $this->actingAs($this->admin(), 'admin');

        Livewire::test(SyncCatalog::class)
            ->fillForm([
                'catalog_file' => [$relativePath],
            ])
            ->call('importAndSync')
            ->assertNotified();

        $this->assertFileExists($this->importedFile);
        $this->assertTrue(Product::query()->where('nome', 'SHAMPOO AUTOMOTIVO')->exists());
        $this->assertFalse(Product::query()->where('nome', 'PRODUTO INATIVO')->value('ativo'));
    }

    public function test_admin_pode_sincronizar_arquivo_ja_importado(): void
    {
        ErpCatalogFileStorage::storeContents(File::get(base_path('tests/fixtures/estoqman/cargapro.txt')));

        $this->actingAs($this->admin(), 'admin');

        Livewire::test(SyncCatalog::class)
            ->call('syncExisting')
            ->assertNotified();

        $this->assertTrue(Product::query()->where('nome', 'CERA DE CARNAUBA')->exists());
    }
}