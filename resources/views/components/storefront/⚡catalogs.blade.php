<?php

use App\Models\Catalog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Catálogos')] class extends Component {
    public function with(): array
    {
        return [
            'catalogos' => Catalog::query()->orderBy('nome')->get(),
        ];
    }
}; ?>

<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold text-accent mb-1">Catálogos</h1>
    <p class="text-stone-500 mb-6">Confira e baixe nossos catálogos de produtos em PDF.</p>

    @if ($catalogos->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-12 text-center">
            <p class="text-stone-500">Nenhum catálogo disponível no momento.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($catalogos as $catalogo)
                <div wire:key="catalogo-{{ $catalogo->id }}"
                     class="flex flex-col rounded-xl border border-stone-200 bg-white p-5 shadow-sm hover:shadow-md transition">
                    <div class="mb-4 flex aspect-[4/3] items-center justify-center overflow-hidden rounded-lg bg-[#ffd10036]">
                        @if ($catalogo->capa_url)
                            <img src="{{ $catalogo->capa_url }}" alt="{{ $catalogo->nome }}" class="h-full w-full object-cover">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-semibold text-stone-800 leading-snug">{{ $catalogo->nome }}</h2>
                        <p class="mt-0.5 text-xs text-stone-400">PDF · {{ $catalogo->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        @if ($catalogo->arquivo_url)
                            <a href="{{ $catalogo->arquivo_url }}" target="_blank" rel="noopener"
                               class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-brand px-3 py-2 text-xs font-semibold text-accent hover:bg-brand/20 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                Visualizar
                            </a>
                            <a href="{{ route('catalogs.download', $catalogo) }}"
                               class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-brand px-3 py-2 text-xs font-semibold text-accent hover:bg-brand-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                Baixar PDF
                            </a>
                        @else
                            <span class="text-xs text-stone-400">Arquivo indisponível</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
