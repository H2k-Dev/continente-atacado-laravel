<?php

namespace App\Filament\Resources\Quotes\Tables;

use App\Models\Quote;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('numero')
                    ->label('Protocolo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cliente_nome')
                    ->label('Cliente')
                    ->description(fn ($record) => $record->empresa)
                    ->searchable(),

                TextColumn::make('telefone')
                    ->label('Contato')
                    ->description(fn ($record) => $record->email),

                TextColumn::make('items_count')
                    ->label('Itens')
                    ->counts('items')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Quote::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'novo' => 'warning',
                        'em_andamento' => 'info',
                        'respondido' => 'success',
                        'fechado' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Recebido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Quote::STATUSES),
            ])
            ->recordActions([
                EditAction::make()->label('Ver / Atender'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
