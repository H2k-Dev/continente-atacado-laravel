<x-mail::message>
# Nova solicitação de orçamento

**Protocolo:** {{ $quote->numero }}
**Recebido em:** {{ $quote->created_at->format('d/m/Y H:i') }}

## Dados do cliente

- **Nome:** {{ $quote->cliente_nome }}
@if ($quote->empresa)
- **Empresa:** {{ $quote->empresa }}
@endif
- **E-mail:** {{ $quote->email }}
- **Telefone:** {{ $quote->telefone }}
@if ($quote->cidade)
- **Cidade:** {{ $quote->cidade }}
@endif

## Itens solicitados

<x-mail::table>
| Produto | Unidade |
|:--------|:--------|
@foreach ($quote->items as $item)
| {{ $item->produto_nome }} | {{ $item->unidade ?: '—' }} |
@endforeach
</x-mail::table>

@if ($quote->mensagem)
## Observações do cliente

{{ $quote->mensagem }}
@endif

<x-mail::button :url="config('app.url')">
Abrir painel administrativo
</x-mail::button>

Responda diretamente a este e-mail para falar com o cliente.

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
