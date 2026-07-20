<?php

namespace App\Filament\Resources\Catalogs\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CatalogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do catálogo')
                    ->columns(1)
                    ->components([
                        TextInput::make('nome')
                            ->label('Nome')
                            ->required()
                            ->maxLength(120),

                        FileUpload::make('arquivo')
                            ->label('Arquivo PDF')
                            ->disk('public')
                            ->directory('catalogos')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf'])
                            ->preserveFilenames()
                            ->openable()
                            ->downloadable()
                            ->required(),

                        FileUpload::make('capa')
                            ->label('Imagem de destaque')
                            ->image()
                            ->disk('public')
                            ->directory('catalogos/capas')
                            ->visibility('public')
                            ->imageEditor()
                            ->helperText('Exibida no cartão do catálogo na loja. Opcional.'),
                    ]),
            ]);
    }
}
