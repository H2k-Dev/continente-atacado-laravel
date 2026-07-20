<?php

namespace App\Filament\Resources\Catalogs\Tables;

use App\Models\Catalog;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CatalogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nome')
            ->columns([
                ImageColumn::make('capa')
                    ->label('')
                    ->disk('public')
                    ->imageHeight(48),

                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('arquivo')
                    ->label('PDF')
                    ->formatStateUsing(fn (?string $state) => $state ? basename($state) : null)
                    ->url(fn (Catalog $record) => $record->arquivo_url, shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedDocumentText),

                TextColumn::make('created_at')
                    ->label('Enviado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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
