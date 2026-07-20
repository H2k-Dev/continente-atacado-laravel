<?php

namespace Tests\Feature;

use App\Mail\QuoteRequested;
use App\Models\Category;
use App\Models\Product;
use App\Models\Quote;
use App\Support\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class QuoteFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function criarProduto(): Product
    {
        $categoria = Category::factory()->create(['nome' => 'Bebidas', 'slug' => 'bebidas']);

        return Product::factory()->create([
            'category_id' => $categoria->id,
            'nome' => 'Refrigerante Cola 2L',
            'slug' => 'refrigerante-cola-2l',
            'ativo' => true,
        ]);
    }

    public function test_home_lista_produtos_ativos(): void
    {
        $produto = $this->criarProduto();

        Livewire::test('storefront.catalog')
            ->assertOk()
            ->assertSee('Refrigerante Cola 2L');
    }

    public function test_adicionar_produto_ao_carrinho_incrementa_contador(): void
    {
        $produto = $this->criarProduto();

        Livewire::test('storefront.catalog')
            ->call('adicionar', $produto->id)
            ->assertDispatched('cart-updated');

        $this->assertSame(1, app(Cart::class)->count());
    }

    public function test_enviar_orcamento_cria_registro_e_dispara_email(): void
    {
        Mail::fake();
        $produto = $this->criarProduto();

        app(Cart::class)->add($produto->id);

        Livewire::test('storefront.cart')
            ->set('cliente_nome', 'João da Silva')
            ->set('empresa', 'Mercadinho do João')
            ->set('email', 'joao@example.com')
            ->set('telefone', '11999998888')
            ->set('cidade', 'São Paulo')
            ->set('mensagem', 'Preciso para entrega semanal.')
            ->set('consentimento', true)
            ->call('enviar')
            ->assertHasNoErrors()
            ->assertSet('numeroEnviado', fn ($v) => is_string($v) && str_starts_with($v, 'ORC-'));

        $this->assertDatabaseCount('quotes', 1);
        $quote = Quote::first();
        $this->assertSame('João da Silva', $quote->cliente_nome);
        $this->assertSame(1, $quote->items()->count());
        $this->assertSame(1, $quote->items()->first()->quantidade);

        Mail::assertSent(QuoteRequested::class, fn ($mail) => $mail->hasTo(config('mail.quote_to')) && $mail->hasCc('joao@example.com'));

        // carrinho esvaziado após envio
        $this->assertTrue(app(Cart::class)->isEmpty());
    }

    public function test_enviar_orcamento_vincula_usuario_logado(): void
    {
        Mail::fake();
        $produto = $this->criarProduto();
        app(Cart::class)->add($produto->id, 2);

        $user = \App\Models\User::factory()->create([
            'name' => 'Maria Souza',
            'email' => 'maria@example.com',
            'telefone' => '47988887777',
        ]);

        Livewire::actingAs($user, 'web')
            ->test('storefront.cart')
            ->set('cidade', 'Joinville')
            ->set('consentimento', true)
            ->call('enviar')
            ->assertHasNoErrors();

        $quote = Quote::first();
        $this->assertSame($user->id, $quote->user_id);
    }

    public function test_meus_orcamentos_lista_solicitacoes_do_usuario(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'maria@example.com',
        ]);

        $quote = Quote::create([
            'user_id' => $user->id,
            'cliente_nome' => $user->name,
            'email' => $user->email,
            'telefone' => '47988887777',
            'status' => 'novo',
        ]);
        $quote->items()->create([
            'produto_nome' => 'Arroz 5kg',
            'unidade' => 'UN',
            'quantidade' => 10,
        ]);

        Quote::create([
            'cliente_nome' => 'Outro Cliente',
            'email' => 'outro@example.com',
            'telefone' => '11999990000',
            'status' => 'novo',
        ]);

        $this->actingAs($user, 'web')
            ->get(route('account.quotes'))
            ->assertOk()
            ->assertSee($quote->numero)
            ->assertSee('Arroz 5kg')
            ->assertDontSee('outro@example.com');
    }

    public function test_formulario_preenche_dados_do_usuario_logado(): void
    {
        $produto = $this->criarProduto();
        app(Cart::class)->add($produto->id, 1);

        $user = \App\Models\User::factory()->create([
            'name' => 'Maria Souza',
            'email' => 'maria@example.com',
            'empresa' => 'Loja da Maria',
            'telefone' => '47988887777',
        ]);

        Livewire::actingAs($user, 'web')
            ->test('storefront.cart')
            ->assertSet('cliente_nome', 'Maria Souza')
            ->assertSet('email', 'maria@example.com')
            ->assertSet('empresa', 'Loja da Maria')
            ->assertSet('telefone', '(47) 98888-7777');
    }

    public function test_validacao_impede_envio_sem_dados(): void
    {
        $produto = $this->criarProduto();
        app(Cart::class)->add($produto->id, 1);

        Livewire::test('storefront.cart')
            ->call('enviar')
            ->assertHasErrors(['cliente_nome', 'email', 'telefone', 'consentimento']);

        $this->assertDatabaseCount('quotes', 0);
    }
}
