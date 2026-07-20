<?php

use App\Models\Product;
use App\Support\Cart;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Produto')] class extends Component {
    public Product $product;

    public function mount(Product $product): void
    {
        abort_unless($product->ativo, 404);

        $this->product = $product->load('category');
    }

    public function adicionar(Cart $cart): void
    {
        $cart->add($this->product->id, 1);
        $this->dispatch('cart-updated');
        $this->dispatch('toast', mensagem: $this->product->nome . ' adicionado ao carrinho.');
    }

    public function with(): array
    {
        return [
            'relacionados' => Product::query()
                ->with('category')
                ->ativos()
                ->where('category_id', $this->product->category_id)
                ->whereKeyNot($this->product->id)
                ->orderByDesc('destaque')
                ->orderBy('ordem')
                ->orderBy('nome')
                ->limit(4)
                ->get(),
        ];
    }
}; ?>

<div>
    <div class="mx-auto max-w-7xl px-4 py-10">
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-stone-500">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-accent transition">Início</a>
            <span>/</span>
            @if ($product->category)
                <a href="{{ route('home', ['categoria' => $product->category->slug]) }}"
                   wire:navigate
                   class="hover:text-accent transition">
                    {{ $product->category->nome }}
                </a>
                <span>/</span>
            @endif
            <span class="text-stone-800">{{ $product->nome }}</span>
        </nav>

        <div class="product-show-layout">
            <div class="bg-white shadow-sm">
                <div class="aspect-square overflow-hidden bg-stone-100 flex items-center justify-center">
                    @if ($product->imagem_url)
                        <img src="{{ $product->imagem_url }}" alt="{{ $product->nome }}" class="h-full w-full object-cover">
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-stone-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    @endif
                </div>
            </div>

            <div>
                @if ($product->marca)
                    <p class="text-sm font-medium uppercase tracking-wide text-accent">{{ $product->marca }}</p>
                @endif

                <h1 class="mt-1 text-3xl font-bold text-accent">{{ $product->nome }}</h1>

                <p class="mt-4 text-2xl font-bold text-accent">Sob consulta</p>

                <dl class="mt-6 space-y-3 text-sm">
                    @if ($product->category)
                        <div class="flex gap-2">
                            <dt class="font-medium text-stone-500">Categoria:</dt>
                            <dd>
                                <a href="{{ route('home', ['categoria' => $product->category->slug]) }}"
                                   wire:navigate
                                   class="text-accent hover:text-accent-700 transition">
                                    {{ $product->category->nome }}
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if ($product->codigo)
                        <div class="flex gap-2">
                            <dt class="font-medium text-stone-500">Código</dt>
                            <dd class="text-stone-800">{{ $product->codigo }}</dd>
                        </div>
                    @endif
                    @if ($product->unidade)
                        <div class="flex gap-2">
                            <dt class="font-medium text-stone-500">Unidade:</dt>
                            <dd class="text-stone-800">{{ $product->unidade }}</dd>
                        </div>
                    @endif
                </dl>

                @if ($product->descricao)
                    <div class="mt-6 rounded-xl">
                        <h2 class="text-sm font-semibold text-stone-700">Descrição</h2>
                        <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $product->descricao }}</p>
                    </div>
                @endif

                <div class="mt-8">
                    <button type="button" wire:click="adicionar"
                            class="inline-flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg bg-brand px-6 py-3 text-sm font-semibold text-accent transition hover:bg-brand-700 sm:flex-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121 0 2.1-.746 2.4-1.822l1.03-3.696A1.125 1.125 0 0 0 20.475 7.5H6.31M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                        </svg>
                        Adicionar ao carrinho
                    </button>
                </div>

                <p class="mt-4 text-xs text-stone-500">
                    Solicite um orçamento sem compromisso. Nossa equipe retorna com condições e disponibilidade.
                </p>
            </div>
        </div>

        @if ($relacionados->isNotEmpty())
            <section class="mt-14">
                <h2 class="text-xl font-bold text-accent">Produtos relacionados</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relacionados as $relacionado)
                        <a href="{{ route('product.show', $relacionado) }}"
                           wire:key="relacionado-{{ $relacionado->id }}"
                           wire:navigate
                           class="flex flex-col rounded-xl border border-stone-200 bg-white p-4 shadow-sm transition hover:shadow-md">
                            <div class="mb-3 flex aspect-square items-center justify-center overflow-hidden rounded-lg bg-stone-100">
                                @if ($relacionado->imagem_url)
                                    <img src="{{ $relacionado->imagem_url }}" alt="{{ $relacionado->nome }}" class="h-full w-full object-cover">
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-stone-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                @endif
                            </div>
                            <h3 class="line-clamp-2 text-sm font-medium leading-snug text-stone-800">{{ $relacionado->nome }}</h3>
                            <p class="mt-2 text-sm font-bold text-accent">Sob consulta</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <div x-data="{ show: false, msg: '' }"
         x-on:toast.window="msg = $event.detail.mensagem; show = true; clearTimeout(window._t); window._t = setTimeout(() => show = false, 2500)"
         x-show="show" x-transition x-cloak
         class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 rounded-lg bg-brand px-5 py-3 text-sm font-medium text-accent shadow-lg">
        <span x-text="msg"></span>
    </div>
</div>