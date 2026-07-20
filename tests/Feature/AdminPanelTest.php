<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_resource_index_pages_carregam(): void
    {
        $cat = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $cat->id, 'barcode' => '7891234567890']);
        $quote = Quote::create([
            'cliente_nome' => 'Teste', 'email' => 't@e.com', 'telefone' => '11999',
            'status' => 'novo',
        ]);
        $quote->items()->create(['produto_nome' => 'X', 'quantidade' => 2]);
        $catalog = Catalog::create(['nome' => 'Catálogo Julho', 'arquivo' => 'catalogos/julho.pdf']);
        $banner = Banner::create(['nome' => 'Banner Principal', 'imagem' => 'banners/principal.jpg']);

        $this->actingAs($this->admin(), 'admin');

        $this->get('/admin/banners')->assertOk();
        $this->get('/admin/banners/create')->assertOk();
        $this->get('/admin/banners/'.$banner->getKey().'/edit')->assertOk();
        $this->get('/admin/categories')->assertOk();
        $this->get('/admin/products')->assertOk();
        $this->get('/admin/products/'.$product->getRouteKey().'/edit')->assertOk();
        $this->get('/admin/catalogs')->assertOk();
        $this->get('/admin/catalogs/create')->assertOk();
        $this->get('/admin/catalogs/'.$catalog->getKey().'/edit')->assertOk();
        $this->get('/admin/quotes')->assertOk();
        $this->get('/admin/quotes/'.$quote->getKey().'/edit')->assertOk();
        $this->get('/admin/sync-catalog')->assertOk();
    }
}
