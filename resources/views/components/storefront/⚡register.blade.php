<?php

use App\Models\User;
use App\Rules\BrazilianPhone;
use App\Support\PhoneMask;
use App\Support\StorefrontAuth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Cadastro')] class extends Component {
    #[Validate('required|string|min:3|max:120')]
    public string $name = '';

    #[Validate('nullable|string|max:120')]
    public string $empresa = '';

    public string $telefone = '';

    #[Validate('required|email|max:150|unique:users,email')]
    public string $email = '';

    #[Validate('required|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    public function updatedTelefone(string $value): void
    {
        $this->telefone = PhoneMask::format($value);
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', Password::defaults()],
            'telefone' => ['nullable', 'string', 'max:15', new BrazilianPhone],
        ];
    }

    public function mount(): void
    {
        if (StorefrontAuth::guard()->check()) {
            $this->redirect(route('account'), navigate: true);
        }
    }

    protected function messages(): array
    {
        return [
            'email.unique' => 'Este e-mail já está cadastrado.',
            'name.required' => 'Informe seu nome.',
        ];
    }

    public function cadastrar(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'empresa' => $this->empresa ?: null,
            'telefone' => $this->telefone ?: null,
            'email' => $this->email,
            'password' => $this->password,
            'role' => User::ROLE_CUSTOMER,
        ]);

        StorefrontAuth::guard()->login($user);
        session()->regenerate();

        $this->redirect(route('home'), navigate: true);
    }
}; ?>

<div class="mx-auto max-w-md px-4 py-12">
    <div class="rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold text-accent">Criar conta</h1>
        <p class="mt-1 text-sm text-stone-500">Cadastre-se para solicitar orçamentos com mais praticidade.</p>

        <form wire:submit="cadastrar" class="mt-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Nome *</label>
                <input type="text" wire:model="name" autocomplete="name"
                       class="storefront-input">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Empresa</label>
                <input type="text" wire:model="empresa" autocomplete="organization"
                       class="storefront-input">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Telefone</label>
                <input type="tel" wire:model.live="telefone" data-phone-mask inputmode="tel"
                       placeholder="(00) 00000-0000" maxlength="15" autocomplete="tel"
                       class="storefront-input">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">E-mail *</label>
                <input type="email" wire:model="email" autocomplete="email"
                       class="storefront-input">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Senha *</label>
                <input type="password" wire:model="password" autocomplete="new-password"
                       class="storefront-input">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Confirmar senha *</label>
                <input type="password" wire:model="password_confirmation" autocomplete="new-password"
                       class="storefront-input">
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-accent px-4 py-3 text-sm font-semibold text-white hover:bg-accent-700 transition"
                    wire:loading.attr="disabled">
                Criar conta
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-500">
            Já tem conta?
            <a href="{{ route('login') }}" class="font-medium text-accent hover:text-accent-700">Entrar</a>
        </p>
    </div>
</div>