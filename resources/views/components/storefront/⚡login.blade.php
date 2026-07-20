<?php

use App\Support\StorefrontAuth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Entrar')] class extends Component {
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (StorefrontAuth::guard()->check()) {
            $this->redirect(route('account'), navigate: true);
        }
    }

    public function entrar(): void
    {
        $this->validate();

        $guard = StorefrontAuth::guard();

        if (! $guard->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'E-mail ou senha incorretos.');

            return;
        }

        if ($guard->user()->isAdmin()) {
            $guard->logout();
            $this->addError('email', 'Contas administrativas devem entrar pelo painel em /admin.');

            return;
        }

        session()->regenerate();

        $this->redirectIntended(default: route('home'), navigate: true);
    }
}; ?>

<div class="mx-auto max-w-md px-4 py-12">
    <div class="rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold text-accent">Entrar</h1>
        <p class="mt-1 text-sm text-stone-500">Acesse sua conta para acompanhar orçamentos.</p>

        <form wire:submit="entrar" class="mt-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">E-mail</label>
                <input type="email" wire:model="email" autocomplete="email" class="storefront-input">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Senha</label>
                <input type="password" wire:model="password" autocomplete="current-password" class="storefront-input">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-stone-600">
                <input type="checkbox" wire:model="remember" class="storefront-checkbox">
                Manter conectado
            </label>

            <button type="submit"
                    class="w-full rounded-lg bg-brand px-4 py-3 text-sm font-semibold text-accent hover:bg-brand-700 transition"
                    wire:loading.attr="disabled">
                Entrar
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-500">
            Ainda não tem conta?
            <a href="{{ route('register') }}" class="font-medium text-accent hover:text-accent-700">Cadastre-se</a>
        </p>
    </div>
</div>