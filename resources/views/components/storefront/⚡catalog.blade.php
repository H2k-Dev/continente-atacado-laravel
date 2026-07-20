<?php

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Support\Cart;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] #[Title('Catálogo')] class extends Component {
    use WithPagination;

    #[Url(as: 'categoria', history: true)]
    public string $categoria = '';

    #[Url(as: 'busca', history: true)]
    public string $busca = '';

    #[Url(as: 'catalogo', history: true)]
    public string $catalogo = '';

    public function updating($name): void
    {
        if (in_array($name, ['categoria', 'busca', 'catalogo'])) {
            $this->resetPage();
        }
    }

    public function adicionar(int $productId, Cart $cart): void
    {
        $cart->add($productId, 1);
        $this->dispatch('cart-updated');

        $produto = Product::find($productId);
        $this->dispatch('toast', mensagem: $produto?->nome . ' adicionado ao carrinho.');
    }

    public function limparFiltros(): void
    {
        $this->reset('categoria', 'busca', 'catalogo');
        $this->resetPage();
    }

    protected function isHomepage(): bool
    {
        return blank($this->categoria) && blank($this->busca) && blank($this->catalogo);
    }

    public function with(): array
    {
        $categorias = Category::query()
            ->where('ativo', true)
            ->withCount(['products' => fn ($q) => $q->where('ativo', true)])
            ->orderBy('ordem')->orderBy('nome')
            ->get();

        $maisVendidos = Product::query()
            ->with('category')
            ->where('ativo', true)
            ->orderByDesc('destaque')
            ->orderBy('ordem')
            ->orderBy('nome')
            ->limit(4)
            ->get();

        $produtos = Product::query()
            ->with('category')
            ->where('ativo', true)
            ->when($this->categoria, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $this->categoria)))
            ->when($this->busca, function ($q) {
                $termo = '%' . $this->busca . '%';
                $q->where(fn ($sub) => $sub->where('nome', 'like', $termo)->orWhere('marca', 'like', $termo));
            })
            ->orderByDesc('destaque')->orderBy('ordem')->orderBy('nome')
            ->paginate(12);

        return [
            'categorias' => $categorias,
            'maisVendidos' => $maisVendidos,
            'produtos' => $produtos,
            'categoriaAtual' => $categorias->firstWhere('slug', $this->categoria),
            'isHomepage' => $this->isHomepage(),
            'banners' => Banner::query()
                ->where('ativo', true)
                ->orderBy('ordem')->orderBy('nome')
                ->get(),
        ];
    }
}; ?>

@php
    $categoryStyles = [
        ['bg' => 'bg-[#ffd10036]', 'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
        ['bg' => 'bg-[#ffd10036]', 'icon' => 'M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 14.5M14.25 3.104c.251.023.501.05.75.082M19.8 14.5l-2.036 5.036a2.25 2.25 0 0 1-2.089 1.455H8.325a2.25 2.25 0 0 1-2.089-1.455L4.2 14.5m15.6 0h-15.6'],
        ['bg' => 'bg-[#ffd10036]', 'icon' => 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z'],
        ['bg' => 'bg-[#ffd10036]', 'icon' => 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z M6 6h.008v.008H6V6Z'],
    ];
@endphp

<div>
    @if ($isHomepage)
        {{-- Hero --}}
        @if ($banners->isNotEmpty())
            <section class="bg-white">
                <div class="mx-auto max-w-7xl px-4 pt-6 lg:pt-8 pb-6 lg:pb-8">
                    <div x-data="{ i: 0, total: {{ $banners->count() }} }"
                         @if ($banners->count() > 1) x-init="setInterval(() => i = (i + 1) % total, 5000)" @endif
                         class="relative">
                        @foreach ($banners as $banner)
                            <div wire:key="banner-{{ $banner->id }}"
                                 x-show="i === {{ $loop->index }}" x-transition
                                 @if (! $loop->first) x-cloak @endif>
                                @if ($banner->link)
                                    <a href="{{ $banner->link }}" class="block overflow-hidden rounded-2xl">
                                        <img src="{{ $banner->imagem_url }}" alt="{{ $banner->nome }}" class="w-full">
                                    </a>
                                @else
                                    <div class="overflow-hidden rounded-2xl">
                                        <img src="{{ $banner->imagem_url }}" alt="{{ $banner->nome }}" class="w-full">
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if ($banners->count() > 1)
                            <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-2">
                                @foreach ($banners as $banner)
                                    <button type="button"
                                            wire:key="banner-dot-{{ $banner->id }}"
                                            @click="i = {{ $loop->index }}"
                                            :class="i === {{ $loop->index }} ? 'bg-accent' : 'bg-white/70'"
                                            class="h-2.5 w-2.5 rounded-full shadow transition"
                                            aria-label="Ir para banner {{ $loop->iteration }}"></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @else
        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-10 lg:py-14">
                <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
                    <div>
                        <span class="inline-block rounded-full bg-accent px-3 py-1 text-xs font-semibold text-white mb-4">
                            Oferta do mês
                        </span>
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-accent leading-tight">
                            Compre no atacado com preço baixo
                        </h1>
                        <p class="mt-4 text-stone-500 text-lg max-w-lg">
                            Produtos em quantidade para sua empresa ou revenda. Pedido simples por e-mail.
                        </p>
                        <a href="#mais-vendidos"
                           class="mt-6 inline-flex items-center gap-2 rounded-lg bg-brand px-6 py-3 text-sm font-semibold text-accent hover:bg-brand-700 transition">
                            Ver promoções
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </div>
                    <div class="rounded-2xl bg-promo p-8 lg:p-10 text-white relative overflow-hidden">
                        <h2 class="text-2xl sm:text-3xl font-bold leading-tight">
                            Abasteça seu negócio e economize mais!
                        </h2>
                        <p class="mt-3 text-white/85 max-w-sm">
                            Grandes marcas, preços imbatíveis e condições especiais para você.
                        </p>
                        <img src="{{ asset('images/hero-cart.svg') }}" alt="Carrinho com produtos"
                             class="mt-6 w-full max-w-sm mx-auto lg:mx-0 lg:absolute lg:right-4 lg:bottom-0 lg:mt-0 lg:max-w-xs">
                    </div>
                </div>
            </div>
        </section>
        @endif

        {{-- Trust bar --}}
        <section class="bg-brand text-accent">
            <div class="mx-auto max-w-7xl px-4 py-8">
                <div class="grid gap-6 sm:grid-cols-3 text-center">
                    <div class="flex flex-col items-center gap-2">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                        </div>
                        <div class="font-bold text-sm tracking-wide">SITE 100% SEGURO</div>
                        <div class="text-xs text-accent/70">NAVEGUE COM TRANQUILIDADE</div>
                    </div>
                    <div class="flex flex-col items-center gap-2">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a49.902 49.902 0 0 0-2.654-9.375A2.25 2.25 0 0 0 18.75 6.75h-13.5a2.25 2.25 0 0 0-2.16 1.587A49.902 49.902 0 0 0 2.25 17.25c-.039.62.469 1.124 1.09 1.124H6.75Z" /></svg>
                        </div>
                        <div class="font-bold text-sm tracking-wide">FRETE GRÁTIS</div>
                        <div class="text-xs text-accent/70">CONSULTE CONDIÇÕES</div>
                    </div>
                    <div class="flex flex-col items-center gap-2">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                        </div>
                        <div class="font-bold text-sm tracking-wide">ORÇAMENTO</div>
                        <div class="text-xs text-accent/70">FACILITADO E COM PRATICIDADE</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Mais vendidos --}}
        <section id="mais-vendidos" class="bg-stone-50">
            <div class="mx-auto max-w-7xl px-4 py-12">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-accent">Mais vendidos</h2>
                    <a href="{{ route('home', ['catalogo' => 'todos']) }}"
                       class="text-sm font-medium text-accent hover:text-accent-700 transition">
                        Ver todos →
                    </a>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($maisVendidos as $produto)
                        <div wire:key="destaque-{{ $produto->id }}"
                             class="flex flex-col rounded-xl border border-stone-200 bg-white p-4 shadow-sm hover:shadow-md transition">
                            <a href="{{ route('product.show', $produto) }}" wire:navigate
                               class="mb-3 block aspect-square overflow-hidden rounded-lg bg-stone-100">
                                @if ($produto->imagem_url)
                                    <img src="{{ $produto->imagem_url }}" alt="{{ $produto->nome }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-stone-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                    </div>
                                @endif
                            </a>
                            <a href="{{ route('product.show', $produto) }}" wire:navigate
                               class="font-medium text-stone-800 text-sm leading-snug line-clamp-2 hover:text-accent transition">
                                {{ $produto->nome }}
                            </a>
                            <p class="mt-2 text-lg font-bold text-accent">Sob consulta</p>
                            @if ($produto->unidade)
                                <p class="text-xs text-stone-400">{{ $produto->unidade }}</p>
                            @endif
                            <button wire:click="adicionar({{ $produto->id }})"
                                    class="mt-3 w-full cursor-pointer rounded-lg bg-brand px-3 py-2 text-xs font-semibold text-accent hover:bg-brand-700 transition">
                                + Adicionar ao carrinho
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Categorias em destaque --}}
        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-12">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-accent">Categorias em destaque</h2>
                    <a href="{{ route('home', ['catalogo' => 'todos']) }}"
                       class="text-sm font-medium text-accent hover:text-accent-700 transition">
                        Ver todas →
                    </a>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($categorias->take(4) as $index => $cat)
                        @php($style = $categoryStyles[$index % count($categoryStyles)])
                        <a href="{{ route('home', ['categoria' => $cat->slug]) }}"
                           wire:key="cat-card-{{ $cat->id }}"
                           class="group flex flex-col items-center rounded-2xl {{ $style['bg'] }} p-8 text-center hover:shadow-md transition">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-sm mb-4 group-hover:scale-105 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $style['icon'] }}" />
                                </svg>
                            </div>
                            <span class="font-semibold text-accent">{{ $cat->nome }}</span>
                            <span class="mt-1 text-xs text-stone-500">{{ $cat->products_count }} produtos</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @else
        {{-- Filtered / full catalog view --}}
        <div class="mx-auto max-w-7xl px-4 py-10">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-accent">
                        @if ($busca)
                            Resultados para "{{ $busca }}"
                        @elseif ($categoriaAtual)
                            {{ $categoriaAtual->nome }}
                        @else
                            Todos os produtos
                        @endif
                    </h1>
                    <p class="mt-1 text-sm text-stone-500">{{ $produtos->total() }} {{ Str::plural('produto', $produtos->total()) }}</p>
                </div>
                @if ($categoria || $busca || $catalogo)
                    <button wire:click="limparFiltros" class="text-sm text-accent hover:text-accent-700 font-medium">
                        Limpar filtros
                    </button>
                @endif
            </div>

            @if ($produtos->isEmpty())
                <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 p-12 text-center text-stone-500">
                    Nenhum produto encontrado.
                    <button wire:click="limparFiltros" class="text-accent hover:underline ml-1">Ver todos</button>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($produtos as $produto)
                        <div wire:key="produto-{{ $produto->id }}"
                             class="flex flex-col rounded-xl border border-stone-200 bg-white p-4 shadow-sm hover:shadow-md transition">
                            <a href="{{ route('product.show', $produto) }}" wire:navigate
                               class="mb-3 block aspect-square overflow-hidden rounded-lg bg-stone-100">
                                @if ($produto->imagem_url)
                                    <img src="{{ $produto->imagem_url }}" alt="{{ $produto->nome }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-stone-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                    </div>
                                @endif
                            </a>
                            @if ($produto->marca)
                                <span class="text-xs font-medium uppercase tracking-wide text-accent">{{ $produto->marca }}</span>
                            @endif
                            <a href="{{ route('product.show', $produto) }}" wire:navigate
                               class="font-medium text-stone-800 text-sm leading-snug line-clamp-2 hover:text-accent transition">
                                {{ $produto->nome }}
                            </a>
                            <p class="mt-2 text-lg font-bold text-accent">Sob consulta</p>
                            @if ($produto->unidade)
                                <p class="text-xs text-stone-400">{{ $produto->unidade }}</p>
                            @endif
                            <button wire:click="adicionar({{ $produto->id }})"
                                    class="mt-3 w-full cursor-pointer rounded-lg bg-brand px-3 py-2 text-xs font-semibold text-accent hover:bg-brand-700 transition">
                                + Adicionar ao carrinho
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $produtos->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Toast --}}
    <div x-data="{ show: false, msg: '' }"
         x-on:toast.window="msg = $event.detail.mensagem; show = true; clearTimeout(window._t); window._t = setTimeout(() => show = false, 2500)"
         x-show="show" x-transition x-cloak
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 rounded-lg bg-brand px-5 py-3 text-sm font-medium text-accent shadow-lg">
        <span x-text="msg"></span>
    </div>
</div>