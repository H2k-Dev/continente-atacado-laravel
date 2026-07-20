<?php

namespace Tests\Feature;

use App\Models\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagina_de_catalogos_carrega_e_lista_itens(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('catalogos/julho.pdf', 'pdf');

        Catalog::create(['nome' => 'Catálogo Julho', 'arquivo' => 'catalogos/julho.pdf']);
        Catalog::create(['nome' => 'Catálogo Agosto', 'arquivo' => 'catalogos/agosto.pdf']);

        $this->get(route('catalogs'))
            ->assertOk()
            ->assertSee('Catálogo Julho')
            ->assertSee('Catálogo Agosto');

        Livewire::test('storefront.catalogs')->assertOk();
    }

    public function test_pagina_de_catalogos_exibe_imagem_de_destaque_quando_presente(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('catalogos/julho.pdf', 'pdf');
        Storage::disk('public')->put('catalogos/capas/julho.jpg', 'img');

        Catalog::create([
            'nome' => 'Catálogo Julho',
            'arquivo' => 'catalogos/julho.pdf',
            'capa' => 'catalogos/capas/julho.jpg',
        ]);

        $this->get(route('catalogs'))
            ->assertOk()
            ->assertSee('/storage/catalogos/capas/julho.jpg');
    }

    public function test_pagina_de_catalogos_exibe_estado_vazio(): void
    {
        $this->get(route('catalogs'))
            ->assertOk()
            ->assertSee('Nenhum catálogo disponível no momento.');
    }

    public function test_download_do_catalogo_retorna_pdf_com_nome_amigavel(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('catalogos/julho.pdf', 'conteudo-pdf');

        $catalogo = Catalog::create(['nome' => 'Catálogo Julho', 'arquivo' => 'catalogos/julho.pdf']);

        $response = $this->get(route('catalogs.download', $catalogo));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=catalogo-julho.pdf');
    }

    public function test_download_retorna_404_quando_arquivo_nao_existe(): void
    {
        Storage::fake('public');

        $catalogo = Catalog::create(['nome' => 'Catálogo Julho', 'arquivo' => 'catalogos/inexistente.pdf']);

        $this->get(route('catalogs.download', $catalogo))->assertNotFound();
    }
}
