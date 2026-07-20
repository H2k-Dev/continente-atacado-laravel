<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da categoria')
                    ->columns(2)
                    ->components([
                        TextInput::make('nome')
                            ->label('Nome')
                            ->required()
                            ->maxLength(120)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set, $get) => filled($get('slug')) ? null : $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->maxLength(140)
                            ->unique(ignoreRecord: true)
                            ->helperText('Gerado automaticamente a partir do nome.'),

                        Textarea::make('descricao')
                            ->label('Descrição')
                            ->rows(3)
                            ->columnSpanFull(),

                        FileUpload::make('imagem')
                            ->label('Imagem')
                            ->image()
                            ->disk('public')
                            ->directory('categorias')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpanFull(),

                        TextInput::make('ordem')
                            ->label('Ordem de exibição')
                            ->numeric()
                            ->default(0),

                        Toggle::make('ativo')
                            ->label('Ativa')
                            ->default(true),
                    ]),
            ]);
    }
}
