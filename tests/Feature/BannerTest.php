<?php

namespace Tests\Feature;

use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    protected function criarBanner(array $attrs = []): Banner
    {
        Storage::disk('public')->put('banners/principal.jpg', 'img');

        return Banner::create(array_merge([
            'nome' => 'Banner Principal',
            'imagem' => 'banners/principal.jpg',
            'ativo' => true,
        ], $attrs));
    }

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_banner_ativo_aparece_na_home(): void
    {
        $this->criarBanner();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('/storage/banners/principal.jpg')
            ->assertDontSee('Compre no atacado com preço baixo');
    }

    public function test_banner_com_link_envolve_a_imagem(): void
    {
        $this->criarBanner(['link' => 'https://example.com/promo']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://example.com/promo');
    }

    public function test_banner_inativo_nao_aparece_e_hero_padrao_e_exibido(): void
    {
        $this->criarBanner(['ativo' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('/storage/banners/principal.jpg')
            ->assertSee('Compre no atacado com preço baixo');
    }

    public function test_sem_banners_exibe_hero_padrao(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Compre no atacado com preço baixo');
    }
}
