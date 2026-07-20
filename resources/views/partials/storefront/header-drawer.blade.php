@php
    $user = auth('web')->user();
@endphp

<div
    class="storefront-drawer"
    :class="{ 'is-open': menuOpen }"
    :aria-hidden="menuOpen ? 'false' : 'true'"
>
    <button
        type="button"
        class="storefront-drawer__backdrop"
        @click="menuOpen = false"
        aria-label="Fechar menu"
    ></button>

    <aside id="storefront-drawer-panel" class="storefront-drawer__panel" role="dialog" aria-modal="true" aria-label="Menu da loja">
        <div class="storefront-drawer__header">
            <div>
                <p class="storefront-drawer__eyebrow">Continente Atacado</p>
                <p class="storefront-drawer__title">
                    @auth('web')
                        Olá, {{ $user->name }}
                    @else
                        Minha conta
                    @endauth
                </p>
            </div>
            <button
                type="button"
                class="storefront-drawer__close"
                @click="menuOpen = false"
                aria-label="Fechar menu"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="storefront-drawer__nav">
            @auth('web')
                <a href="{{ route('account') }}" class="storefront-drawer__link" @click="menuOpen = false">
                    Meus dados
                </a>
                <a href="{{ route('account.quotes') }}" class="storefront-drawer__link" @click="menuOpen = false">
                    Meus orçamentos
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="storefront-drawer__link storefront-drawer__button">
                        Sair
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="storefront-drawer__link" @click="menuOpen = false">
                    Entrar
                </a>
                <a href="{{ route('register') }}" class="storefront-drawer__link storefront-drawer__link--accent" @click="menuOpen = false">
                    Cadastrar
                </a>
            @endauth

            <div class="storefront-drawer__divider"></div>

            <a href="{{ route('catalogs') }}" class="storefront-drawer__link" @click="menuOpen = false">
                Catálogos
            </a>
            <a href="{{ route('cart') }}" class="storefront-drawer__link" @click="menuOpen = false">
                Carrinho (<livewire:storefront.cart-counter />)
            </a>
        </nav>
    </aside>
</div>