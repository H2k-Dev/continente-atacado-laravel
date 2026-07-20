@props(['active' => 'dados'])

@php
    $linkClass = fn (string $section) => $active === $section
        ? 'bg-brand/20 text-accent font-semibold'
        : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900';
@endphp

<div class="mx-auto max-w-7xl px-4 py-12">
    <div class="flex flex-row items-start gap-4 sm:gap-6 lg:gap-8">
        <aside class="w-52 shrink-0 sm:w-56">
            <nav class="rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-400">Minha conta</p>

                <ul class="mt-3 space-y-1">
                    <li>
                        <a href="{{ route('account') }}"
                           wire:navigate
                           class="flex items-center gap-2 rounded-lg py-2 text-sm transition {{ $linkClass('dados') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <span class="leading-tight">Meus dados</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('account.quotes') }}"
                           wire:navigate
                           class="flex items-center gap-2 rounded-lg py-2 text-sm transition {{ $linkClass('orcamentos') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span class="leading-tight">Meus orçamentos</span>
                        </a>
                    </li>
                </ul>

                <div class="mt-4 border-t border-stone-100 pt-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-2 rounded-lg py-2 text-sm text-stone-600 transition hover:bg-stone-50 hover:text-stone-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                            </svg>
                            <span>Sair</span>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        <main class="min-w-0 flex-1">
            {{ $slot }}
        </main>
    </div>
</div>