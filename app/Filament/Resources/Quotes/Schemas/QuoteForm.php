<?php

namespace App\Filament\Resources\Quotes\Schemas;

use App\Models\Quote;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Solicitação')
                    ->columnSpanFull()
                    ->columns(3)
                    ->components([
                        TextInput::make('numero')
                            ->label('Protocolo')
                            ->disabled(),

                        Select::make('status')
                            ->label('Status')
                            ->options(Quote::STATUSES)
                            ->default('novo')
                            ->required()
                            ->native(false),

                        TextInput::make('created_at')
                            ->label('Recebido em')
                            ->formatStateUsing(fn ($state) => $state ? \Illuminate\Support\Carbon::parse($state)->format('d/m/Y H:i') : null)
                            ->disabled(),
                    ]),

                Section::make('Dados do cliente')
                    ->columnSpanFull()
                    ->columns(2)
                    ->components([
                        TextInput::make('cliente_nome')->label('Nome')->disabled(),
                        TextInput::make('empresa')->label('Empresa')->disabled(),
                        TextInput::make('email')->label('E-mail')->disabled(),
                        TextInput::make('telefone')->label('Telefone')->disabled(),
                        TextInput::make('cidade')->label('Cidade')->disabled(),
                        Textarea::make('mensagem')->label('Observações do cliente')->disabled()->columnSpanFull(),
                    ]),

                Section::make('Itens do orçamento')
                    ->columnSpanFull()
                    ->components([
                        Repeater::make('items')
                            ->label('')
                            ->relationship('items')
                            ->columns(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->schema([
                                TextInput::make('produto_nome')->label('Produto')->disabled()->columnSpan(2),
                                TextInput::make('unidade')->label('Unidade')->disabled(),
                            ]),
                    ]),
            ]);
    }
}
