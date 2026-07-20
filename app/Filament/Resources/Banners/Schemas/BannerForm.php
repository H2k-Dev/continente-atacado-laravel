<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do banner')
                    ->columns(2)
                    ->components([
                        TextInput::make('nome')
                            ->label('Nome')
                            ->required()
                            ->maxLength(120)
                            ->helperText('Identificação interna do banner.'),

                        TextInput::make('link')
                            ->label('Link (URL)')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Para onde o banner leva ao clicar. Opcional.'),

                        FileUpload::make('imagem')
                            ->label('Imagem')
                            ->image()
                            ->disk('public')
                            ->directory('banners')
                            ->visibility('public')
                            ->imageEditor()
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Recomendado: 1920×600 px.'),

                        TextInput::make('ordem')
                            ->label('Ordem de exibição')
                            ->numeric()
                            ->default(0),

                        Toggle::make('ativo')
                            ->label('Ativo')
                            ->default(true),
                    ]),
            ]);
    }
}
