<?php

use App\Mail\QuoteRequested;
use App\Models\Quote;
use App\Models\User;
use App\Rules\BrazilianPhone;
use App\Support\Cart;
use App\Support\PhoneMask;
use App\Support\StorefrontAuth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Carrinho')] class extends Component {
    #[Validate('required|string|min:3|max:120')]
    public string $cliente_nome = '';

    #[Validate('nullable|string|max:120')]
    public string $empresa = '';

    #[Validate('required|email|max:150')]
    public string $email = '';

    public string $telefone = '';

    #[Validate('nullable|string|max:120')]
    public string $cidade = '';

    #[Validate('nullable|string|max:2000')]
    public string $mensagem = '';

    #[Validate('accepted')]
    public bool $consentimento = false;

    public ?string $numeroEnviado = null;

    public function mount(): void
    {
        $this->preencherDadosDoUsuario();
    }

    protected function preencherDadosDoUsuario(): void
    {
        $user = StorefrontAuth::guard()->user();

        if (! $user instanceof User) {
            return;
        }

        $this->cliente_nome = $user->name;
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
        return [
            'telefone' => ['required', 'string', 'max:15', new BrazilianPhone],
        ];
    }

    protected function messages(): array
    {
        return [
            'consentimento.accepted' => 'É necessário aceitar para enviarmos o orçamento.',
            'cliente_nome.required' => 'Informe seu nome.',
            'email.required' => 'Informe um e-mail para contato.',
            'telefone.required' => 'Informe um telefone para contato.',
        ];
    }

    public function remover(int $productId, Cart $cart): void
    {
        $cart->remove($productId);
        $this->dispatch('cart-updated');
    }

    public function esvaziar(Cart $cart): void
    {
        $cart->clear();
        $this->dispatch('cart-updated');
    }

    public function enviar(Cart $cart): void
    {
        $this->validate();

        if ($cart->isEmpty()) {
            $this->addError('carrinho', 'Adicione ao menos um produto antes de enviar.');
            return;
        }

        $user = StorefrontAuth::guard()->user();

        $quote = Quote::create([
            'user_id' => $user instanceof User ? $user->id : null,
            'cliente_nome' => $this->cliente_nome,
            'empresa' => $this->empresa ?: null,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'cidade' => $this->cidade ?: null,
            'mensagem' => $this->mensagem ?: null,
            'status' => 'novo',
        ]);

        foreach ($cart->lines() as $linha) {
            $quote->items()->create([
                'product_id' => $linha['product']->id,
                'produto_nome' => $linha['product']->nome,
                'unidade' => $linha['product']->unidade,
                'quantidade' => $linha['quantidade'],
                'observacao' => $linha['observacao'] ?? null,
            ]);
        }

        $quote->load('items');

        Mail::to(config('mail.quote_to'))
            ->cc($this->email)
            ->send(new QuoteRequested($quote));

        $cart->clear();
        $this->dispatch('cart-updated');
        $this->numeroEnviado = $quote->numero;
        $this->reset(['cliente_nome', 'empresa', 'email', 'telefone', 'cidade', 'mensagem', 'consentimento']);
        $this->preencherDadosDoUsuario();
    }

    public function with(Cart $cart): array
    {
        return [
            'linhas' => $cart->lines(),
            'totalItens' => $cart->count(),
        ];
    }
}; ?>

<div class="mx-auto max-w-5xl px-4 py-10">
    @if ($numeroEnviado)
        {{-- Confirmação --}}
        <div class="mx-auto max-w-lg rounded-2xl border border-brand-200 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-brand-100 text-accent">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
            </div>
            <h1 class="text-2xl font-bold text-stone-800">Orçamento enviado!</h1>
            <p class="mt-2 text-stone-500">
                Recebemos sua solicitação sob o protocolo
                <span class="font-semibold text-stone-700">{{ $numeroEnviado }}</span>.
                Nossa equipe entrará em contato em breve pelo e-mail e telefone informados.
            </p>
            <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-white hover:bg-accent-700">
                Voltar aos produtos
            </a>
        </div>
    @else
        <h1 class="text-2xl font-bold text-stone-800 mb-1">Meu orçamento</h1>
        <p class="text-stone-500 mb-6">Revise os itens e envie sua solicitação. Sem compromisso de compra.</p>

        @if ($linhas->isEmpty())
            <div class="rounded-xl border border-dashed border-stone-300 bg-white p-12 text-center">
                <p class="text-stone-500">Sua lista de orçamento está vazia.</p>
                <a href="{{ route('home') }}" class="mt-4 inline-flex rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-white hover:bg-accent-700">Ver produtos</a>
            </div>
        @else
            <div class="grid gap-8 lg:grid-cols-[1fr_360px] items-start">
                {{-- Itens --}}
                <div class="rounded-xl border border-stone-200 bg-white divide-y divide-stone-100">
                    @foreach ($linhas as $linha)
                        @php($produto = $linha['product'])
                        <div wire:key="cart-{{ $produto->id }}" class="flex gap-4 p-4">
                            <div class="h-24 w-24 flex-shrink-0 rounded-lg bg-stone-100 overflow-hidden flex items-center justify-center">
                                @if ($produto->imagem_url)
                                    <img src="{{ $produto->imagem_url }}" alt="{{ $produto->nome }}" class="h-full w-full object-cover">
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-stone-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 19.5h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="font-semibold text-stone-800">{{ $produto->nome }}</h3>
                                        <p class="text-xs text-stone-500">
                                            {{ $produto->category?->nome }}@if ($produto->unidade) · {{ $produto->unidade }}@endif
                                        </p>
                                    </div>
                                    <button wire:click="remover({{ $produto->id }})" class="text-stone-400 hover:text-red-600" title="Remover">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between p-4 text-sm">
                        <span class="text-stone-500">{{ $totalItens }} {{ Str::plural('item', $totalItens) }} no total</span>
                        <button wire:click="esvaziar" wire:confirm="Remover todos os itens?" class="text-red-600 hover:underline">Esvaziar lista</button>
                    </div>
                </div>

                {{-- Formulário --}}
                <form wire:submit="enviar" class="rounded-xl border border-stone-200 bg-white p-6 space-y-4 lg:sticky lg:top-20">
                    <h2 class="font-bold text-stone-800">Seus dados</h2>

                    @error('carrinho') <p class="rounded bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p> @enderror

                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Nome *</label>
                        <input type="text" wire:model="cliente_nome" class="storefront-input">
                        @error('cliente_nome') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Empresa</label>
                        <input type="text" wire:model="empresa" class="storefront-input">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">E-mail *</label>
                            <input type="email" wire:model="email" class="storefront-input">
                            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-stone-700 mb-1">Telefone *</label>
                            <input type="tel" wire:model.live="telefone" data-phone-mask inputmode="tel"
                                   placeholder="(00) 00000-0000" maxlength="15" autocomplete="tel"
                                   class="storefront-input">
                            @error('telefone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Cidade</label>
                        <input type="text" wire:model="cidade" class="storefront-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Observações</label>
                        <textarea wire:model="mensagem" rows="3" placeholder="Prazo de entrega, condições, etc."
                                  class="storefront-input"></textarea>
                    </div>
                    <label class="flex items-start gap-2 text-xs text-stone-500">
                        <input type="checkbox" wire:model="consentimento" class="storefront-checkbox mt-0.5">
                        <span>Autorizo o contato para tratativa deste orçamento.</span>
                    </label>
                    @error('consentimento') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                    <button type="submit"
                            class="w-full rounded-lg bg-accent px-4 py-3 text-sm font-semibold text-white hover:bg-accent-700 disabled:opacity-60"
                            wire:loading.attr="disabled" wire:target="enviar">
                        <span wire:loading.remove wire:target="enviar">Enviar solicitação de orçamento</span>
                        <span wire:loading wire:target="enviar">Enviando...</span>
                    </button>
                    <p class="text-center text-xs text-stone-400">Sem compromisso de compra.</p>
                </form>
            </div>
        @endif
    @endif
</div>
