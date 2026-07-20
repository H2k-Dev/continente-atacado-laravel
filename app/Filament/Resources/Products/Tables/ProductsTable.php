<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nome')
            ->columns([
                ImageColumn::make('imagem')
                    ->label('')
                    ->disk('public')
                    ->square(),

                TextColumn::make('nome')
                    ->label('Produto')
                    ->description(fn ($record) => $record->marca)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.nome')
                    ->label('Categoria')
                    ->badge()
                    ->sortable(),

                TextColumn::make('unidade')
                    ->label('Unidade')
                    ->toggleable(),

                IconColumn::make('destaque')
                    ->label('Destaque')
                    ->boolean(),

                ToggleColumn::make('ativo')
                    ->label('Ativo'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'nome')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('ativo')
                    ->label('Ativo'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
