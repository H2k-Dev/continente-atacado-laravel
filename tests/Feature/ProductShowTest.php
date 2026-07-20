<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Support\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use RefreshDatabase;

    protected function criarProduto(array $attrs = []): Product
    {
        $categoria = Category::factory()->create(['nome' => 'Bebidas', 'slug' => 'bebidas']);

        return Product::factory()->create(array_merge([
            'category_id' => $categoria->id,
            'nome' => 'Refrigerante Cola 2L',
            'slug' => 'refrigerante-cola-2l',
            'descricao' => 'Refrigerante cola em garrafa de 2 litros.',
            'codigo' => 'COLA-2L',
            'ativo' => true,
        ], $attrs));
    }

    public function test_pagina_de_produto_carrega(): void
    {
        $produto = $this->criarProduto();

        $this->get(route('product.show', $produto))
            ->assertOk()
            ->assertSee('Refrigerante Cola 2L')
            ->assertSee('Refrigerante cola em garrafa de 2 litros.')
            ->assertSee('COLA-2L')
            ->assertSee('Bebidas');
    }

    public function test_produto_inativo_retorna_404(): void
    {
        $produto = $this->criarProduto(['ativo' => false]);

        $this->get(route('product.show', $produto))->assertNotFound();
    }

    public function test_catalogo_linka_para_pagina_do_produto(): void
    {
        $produto = $this->criarProduto();

        $this->get(route('home', ['catalogo' => 'todos']))
            ->assertOk()
            ->assertSee(route('product.show', $produto), false);
    }

    public function test_adicionar_ao_carrinho_na_pagina_do_produto(): void
    {
        $produto = $this->criarProduto();

        Livewire::test('storefront.product-show', ['product' => $produto])
            ->call('adicionar')
            ->assertDispatched('cart-updated');

        $this->assertSame(1, app(Cart::class)->quantityFor($produto->id));
    }
}