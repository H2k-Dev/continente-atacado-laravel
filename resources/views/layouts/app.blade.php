@props(['title' => null])

@php
    $navCategories = \App\Models\Category::query()
        ->where('ativo', true)
        ->orderBy('ordem')->orderBy('nome')
        ->get();
    $categoriaAtiva = request('categoria', '');
@endphp

<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title . ' — ' : '' }}{{ config('app.name') }}</title>
    <meta name="description" content="Distribuidora e atacado — monte sua lista e solicite um orçamento sem compromisso.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body
    class="min-h-screen bg-white text-stone-800 antialiased flex flex-col"
    x-data="{ menuOpen: false }"
    :class="{ 'overflow-hidden': menuOpen }"
    @keydown.escape.window="menuOpen = false"
>
    <header class="sticky top-0 z-40 bg-brand text-accent shadow-md">
        <div class="mx-auto max-w-7xl px-4">
            {{-- Top bar: logo + search + actions --}}
            <div class="flex items-center justify-between gap-3 py-3">
                <a href="{{ route('home') }}" class="flex items-center shrink-0" aria-label="Continente Atacado">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="Continente Atacado"
                         class="h-9 w-auto sm:h-11"
                         width="180"
                         height="44">
                </a>

                <form action="{{ route('home') }}" method="GET" class="flex-1 max-w-xl mx-auto hidden sm:block">
                    @if ($categoriaAtiva)
                        <input type="hidden" name="categoria" value="{{ $categoriaAtiva }}">
                    @endif
                    <div class="relative">
                        <input type="search" name="busca" value="{{ request('busca', '') }}"
                               placeholder="Buscar produtos, marcas ou categorias..."
                               class="storefront-search pl-11">
                        <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-4 top-2.5 h-5 w-5 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                </form>

                <div class="storefront-header-mobile-actions">
                    <a href="{{ route('catalogs') }}"
                       class="storefront-header-icon-btn"
                       aria-label="Catálogos">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </a>
                    <a href="{{ route('cart') }}"
                       class="storefront-header-icon-btn storefront-header-icon-btn--accent"
                       aria-label="Carrinho">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121 0 2.1-.746 2.4-1.822l1.03-3.696A1.125 1.125 0 0 0 20.475 7.5H6.31M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                        </svg>
                        <span>(<livewire:storefront.cart-counter />)</span>
                    </a>
                    <button
                        type="button"
                        class="storefront-header-icon-btn"
                        @click="menuOpen = true"
                        aria-label="Abrir menu"
                        aria-controls="storefront-drawer-panel"
                        :aria-expanded="menuOpen ? 'true' : 'false'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>

                <div class="storefront-header-desktop-actions">
                    @auth('web')
                        <a href="{{ route('account') }}"
                           class="storefront-header-auth"
                           title="Minha conta">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <span class="max-w-[10rem] truncate">{{ auth('web')->user()->name }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="storefront-header-auth" title="Entrar">
                            Entrar
                        </a>
                        <a href="{{ route('register') }}" class="storefront-header-auth storefront-header-auth--muted">
                            Cadastrar
                        </a>
                    @endauth
                    <a href="{{ route('catalogs') }}" class="storefront-header-auth">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                        Catálogos
                    </a>
                    <a href="{{ route('cart') }}" class="storefront-header-cart">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121 0 2.1-.746 2.4-1.822l1.03-3.696A1.125 1.125 0 0 0 20.475 7.5H6.31M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                        </svg>
                        Carrinho (<livewire:storefront.cart-counter />)
                    </a>
                </div>
            </div>

            {{-- Mobile search --}}
            <form action="{{ route('home') }}" method="GET" class="pb-3 sm:hidden">
                @if ($categoriaAtiva)
                    <input type="hidden" name="categoria" value="{{ $categoriaAtiva }}">
                @endif
                <div class="relative">
                    <input type="search" name="busca" value="{{ request('busca', '') }}"
                           placeholder="Buscar produtos..."
                           class="storefront-search pl-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3.5 top-2.5 h-5 w-5 text-stone-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
            </form>

            {{-- Category pills --}}
            <nav class="flex items-center gap-2 pb-3 overflow-x-auto scrollbar-none">
                <a href="{{ route('home') }}"
                   @class([
                       'shrink-0 rounded-full px-4 py-1.5 text-sm font-medium transition',
                       'bg-accent text-white' => $categoriaAtiva === '',
                       'bg-brand-700 text-accent/90 hover:bg-brand-600' => $categoriaAtiva !== '',
                   ])>
                    Todos
                </a>
                @foreach ($navCategories as $cat)
                    <a href="{{ route('home', ['categoria' => $cat->slug]) }}"
                       @class([
                           'shrink-0 rounded-full px-4 py-1.5 text-sm font-medium transition',
                           'bg-accent text-white' => $categoriaAtiva === $cat->slug,
                           'bg-brand-700 text-accent/90 hover:bg-brand-600' => $categoriaAtiva !== $cat->slug,
                       ])>
                        {{ $cat->nome }}
                    </a>
                @endforeach
            </nav>
        </div>
    </header>

    @include('partials.storefront.header-drawer')

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="bg-brand text-accent mt-auto">
        <div class="mx-auto max-w-7xl px-4 py-12 grid gap-8 sm:grid-cols-3 text-sm">
            <div>
                <div class="font-bold text-base mb-3">Continente Atacado</div>
                <ul class="space-y-2 text-accent/70">
                    <li><a href="{{ route('catalogs') }}" class="hover:text-accent transition">Catálogos</a></li>
                    <li><a href="#" class="hover:text-accent transition">Sobre nós</a></li>
                    <li><a href="#" class="hover:text-accent transition">Contato</a></li>
                    <li><a href="#" class="hover:text-accent transition">Política de privacidade</a></li>
                    <li><a href="#" class="hover:text-accent transition">Termos de uso</a></li>
                </ul>
            </div>
            <div>
                <div class="font-bold text-base mb-3">Minha conta</div>
                <ul class="space-y-2 text-accent/70">
                    @auth('web')
                        <li><a href="{{ route('account') }}" class="hover:text-accent transition">Minha conta</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="hover:text-accent transition">Sair</button>
                            </form>
                        </li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-accent transition">Entrar</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-accent transition">Cadastrar</a></li>
                    @endauth
                    <li><a href="{{ route('cart') }}" class="hover:text-accent transition">Meu orçamento</a></li>
                    <li><a href="#" class="hover:text-accent transition">Favoritos</a></li>
                </ul>
            </div>
            <div>
                <div class="font-bold text-base mb-3">Atendimento</div>
                <ul class="space-y-2 text-accent/70">
                    <li>(47) 9xxxx-xxxx</li>
                    <li>contato@continente.com.br</li>
                    <li>Seg–Sex, 8h às 18h</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-accent/10 py-4 text-center text-xs text-accent/50">
            © {{ date('Y') }} Continente Atacado. Todos os direitos reservados.
        </div>
    </footer>

    @livewireScripts
</body>
</html>