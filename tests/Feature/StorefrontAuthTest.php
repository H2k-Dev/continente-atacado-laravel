<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StorefrontAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginas_de_login_e_cadastro_carregam(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Entrar');
        $this->get(route('register'))->assertOk()->assertSee('Criar conta');
    }

    public function test_cliente_pode_se_cadastrar(): void
    {
        Livewire::test('storefront.register')
            ->set('name', 'João Silva')
            ->set('empresa', 'Mercado do João')
            ->set('telefone', '47999998888')
            ->set('email', 'joao@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('cadastrar')
            ->assertRedirect(route('home'));

        $this->assertAuthenticated('web');
        $this->assertDatabaseHas('users', [
            'email' => 'joao@example.com',
            'role' => User::ROLE_CUSTOMER,
            'empresa' => 'Mercado do João',
        ]);
    }

    public function test_cliente_pode_entrar(): void
    {
        $user = User::factory()->create([
            'email' => 'cliente@example.com',
            'password' => 'password123',
        ]);

        Livewire::test('storefront.login')
            ->set('email', 'cliente@example.com')
            ->set('password', 'password123')
            ->call('entrar')
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_minha_conta_exige_autenticacao(): void
    {
        $this->get(route('account'))->assertRedirect(route('login'));

        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->get(route('account'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('Meus dados')
            ->assertSee('Meus orçamentos');
    }

    public function test_meus_orcamentos_exige_autenticacao(): void
    {
        $this->get(route('account.quotes'))->assertRedirect(route('login'));
    }

    public function test_cliente_pode_atualizar_dados_na_conta(): void
    {
        $user = User::factory()->create([
            'name' => 'Maria Souza',
            'email' => 'maria@example.com',
            'empresa' => 'Loja da Maria',
            'telefone' => '47988887777',
        ]);

        Livewire::actingAs($user, 'web')
            ->test('storefront.account')
            ->set('name', 'Maria Souza Atualizada')
            ->set('empresa', 'Nova Empresa')
            ->set('email', 'maria.nova@example.com')
            ->set('telefone', '47999990000')
            ->call('salvar')
            ->assertHasNoErrors()
            ->assertSet('salvo', true)
            ->assertSee('Seus dados foram atualizados com sucesso');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Maria Souza Atualizada',
            'email' => 'maria.nova@example.com',
            'empresa' => 'Nova Empresa',
            'telefone' => '(47) 99999-0000',
        ]);
    }

    public function test_atualizacao_de_email_nao_permite_duplicado(): void
    {
        $user = User::factory()->create(['email' => 'maria@example.com']);
        User::factory()->create(['email' => 'outro@example.com']);

        Livewire::actingAs($user, 'web')
            ->test('storefront.account')
            ->set('email', 'outro@example.com')
            ->call('salvar')
            ->assertHasErrors(['email']);
    }

    public function test_cliente_nao_acessa_painel_filament(): void
    {
        $customer = User::factory()->create();

        $this->assertFalse($customer->canAccessPanel(filament()->getPanel('admin')));
    }

    public function test_admin_acessa_painel_filament(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->canAccessPanel(filament()->getPanel('admin')));
    }

    public function test_logout_encerra_sessao(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest('web');
    }

    public function test_sessao_admin_nao_aparece_no_storefront(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Secret']);

        $this->actingAs($admin, 'admin')
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('Admin Secret')
            ->assertSee('Entrar')
            ->assertSee('Abrir menu', false);

        $this->assertGuest('web');
        $this->assertAuthenticated('admin');
    }

    public function test_admin_nao_pode_entrar_pelo_storefront(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        Livewire::test('storefront.login')
            ->set('email', 'admin@example.com')
            ->set('password', 'password123')
            ->call('entrar')
            ->assertHasErrors(['email']);

        $this->assertGuest('web');
    }
}