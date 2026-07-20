<?php

use App\Models\Quote;
use App\Support\StorefrontAuth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('Meus orçamentos')] class extends Component {
    public function mount(): void
    {
        if (! StorefrontAuth::guard()->check()) {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function with(): array
    {
        $user = StorefrontAuth::guard()->user();

        return [
            'quotes' => Quote::query()
                ->forUser($user)
                ->withCount('items')
                ->with('items')
                ->latest()
                ->get(),
        ];
    }
}; ?>

@php
    $statusStyles = [
        'novo' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'em_andamento' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'respondido' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'fechado' => 'bg-stone-100 text-stone-600 ring-stone-500/20',
    ];
@endphp

<x-storefront.account-layout active="orcamentos">
    <div class="rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold text-accent">Meus orçamentos</h1>
        <p class="mt-1 text-sm text-stone-500">Solicitações de orçamento enviadas por você.</p>

        @if ($quotes->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-stone-200 bg-stone-50 px-6 py-10 text-center">
                <p class="text-sm text-stone-600">Você ainda não enviou nenhuma solicitação de orçamento.</p>
                <a href="{{ route('cart') }}"
                   class="mt-4 inline-flex rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-accent transition hover:bg-brand-700">
                    Montar orçamento
                </a>
            </div>
        @else
            <div class="mt-6 space-y-4">
                @foreach ($quotes as $quote)
                    <article class="rounded-xl border border-stone-200 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="font-semibold text-stone-900">{{ $quote->numero }}</p>
                                <p class="mt-0.5 text-sm text-stone-500">
                                    Enviado em {{ $quote->created_at->timezone(config('app.timezone'))->format('d/m/Y \à\s H:i') }}
                                </p>
                            </div>
                            <span class="inline-flex w-fit items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusStyles[$quote->status] ?? $statusStyles['fechado'] }}">
                                {{ $quote->status_label }}
                            </span>
                        </div>

                        <p class="mt-3 text-sm text-stone-600">
                            {{ $quote->items_count }} {{ $quote->items_count === 1 ? 'item' : 'itens' }}
                            @if ($quote->cidade)
                                · {{ $quote->cidade }}
                            @endif
                        </p>

                        @if ($quote->mensagem)
                            <p class="mt-2 text-sm text-stone-500">{{ $quote->mensagem }}</p>
                        @endif

                        <ul class="mt-4 divide-y divide-stone-100 rounded-lg border border-stone-100 bg-stone-50/50">
                            @foreach ($quote->items as $item)
                                <li class="flex items-center justify-between gap-4 px-4 py-2.5 text-sm">
                                    <span class="text-stone-800">{{ $item->produto_nome }}</span>
                                    @if ($item->unidade)
                                        <span class="shrink-0 text-stone-500">{{ $item->unidade }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-storefront.account-layout>