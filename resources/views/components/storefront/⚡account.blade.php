<?php

use App\Rules\BrazilianPhone;
use App\Support\PhoneMask;
use App\Support\StorefrontAuth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Minha conta')] class extends Component {
    public string $name = '';

    public string $empresa = '';

    public string $telefone = '';

    public string $email = '';

    public bool $salvo = false;

    public function mount(): void
    {
        if (! StorefrontAuth::guard()->check()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $user = StorefrontAuth::guard()->user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->empresa = $user->empresa ?? '';
        $this->telefone = PhoneMask::format($user->telefone ?? '');
    }

    public function updatedTelefone(string $value): void
    {
        $this->telefone = PhoneMask::format($value);
    }

    public function rules(): array
    {
        $userId = StorefrontAuth::guard()->id();

        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'empresa' => ['nullable', 'string', 'max:120'],
            'telefone' => ['nullable', 'string', 'max:15', new BrazilianPhone],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.unique' => 'Este e-mail já está cadastrado.',
            'name.required' => 'Informe seu nome.',
        ];
    }

    public function salvar(): void
    {
        $this->validate();

        $user = StorefrontAuth::guard()->user();

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'empresa' => $this->empresa ?: null,
            'telefone' => $this->telefone ?: null,
        ]);

        $this->salvo = true;
    }
}; ?>

<x-storefront.account-layout active="dados">
    <div class="rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold text-accent">Meus dados</h1>
        <p class="mt-1 text-sm text-stone-500">Atualize seus dados de cadastro no site.</p>

        @if ($salvo)
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                Seus dados foram atualizados com sucesso.
            </p>
        @endif

        <form wire:submit="salvar" class="mt-6 space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">Nome *</label>
                <input type="text" wire:model="name" autocomplete="name" class="storefront-input">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">Empresa</label>
                <input type="text" wire:model="empresa" autocomplete="organization" class="storefront-input">
                @error('empresa') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">E-mail *</label>
                    <input type="email" wire:model="email" autocomplete="email" class="storefront-input">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">Telefone</label>
                    <input type="tel" wire:model.live="telefone" data-phone-mask inputmode="tel"
                           placeholder="(00) 00000-0000" maxlength="15" autocomplete="tel"
                           class="storefront-input">
                    @error('telefone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center">
                <button type="submit"
                        class="inline-flex justify-center rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-accent transition hover:bg-brand-700"
                        wire:loading.attr="disabled" wire:target="salvar">
                    <span wire:loading.remove wire:target="salvar">Salvar alterações</span>
                    <span wire:loading wire:target="salvar">Salvando...</span>
                </button>
                <a href="{{ route('cart') }}"
                   class="inline-flex justify-center rounded-lg border border-stone-300 px-4 py-2.5 text-sm font-medium text-stone-700 transition hover:bg-stone-50">
                    Novo orçamento
                </a>
            </div>
        </form>
    </div>
</x-storefront.account-layout>