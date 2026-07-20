<?php

namespace App\Filament\Resources\Banners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BannersTable
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
                    ->imageHeight(48),

                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('link')
                    ->label('Link')
                    ->limit(40)
                    ->placeholder('—'),

                TextColumn::make('ordem')
                    ->label('Ordem')
                    ->sortable(),

                ToggleColumn::make('ativo')
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
