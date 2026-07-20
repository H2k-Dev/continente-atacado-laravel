<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('ordem')
            ->reorderable('ordem')
            ->columns([
                ImageColumn::make('imagem')
                    ->label('')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('products_count')
                    ->label('Produtos')
                    ->counts('products')
                    ->badge(),

                TextColumn::make('ordem')
                    ->label('Ordem')
                    ->sortable(),

                ToggleColumn::make('ativo')
                    ->label('Ativa'),
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
