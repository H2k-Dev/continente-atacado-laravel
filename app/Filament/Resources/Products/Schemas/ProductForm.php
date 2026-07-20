<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações do produto')
                    ->columns(2)
                    ->components([
                        Select::make('category_id')
                            ->label('Categoria')
                            ->relationship('category', 'nome')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('marca')
                            ->label('Marca')
                            ->maxLength(120),

                        TextInput::make('nome')
                            ->label('Nome')
                            ->required()
                            ->maxLength(160)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set, $get) => filled($get('slug')) ? null : $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->maxLength(180)
                            ->unique(ignoreRecord: true)
                            ->helperText('Gerado automaticamente a partir do nome.'),

                        TextInput::make('barcode')
                            ->label('Código de barras')
                            ->maxLength(64)
                            ->helperText('Preenchido automaticamente na sincronização com o ERP.'),

                        TextInput::make('unidade')
                            ->label('Unidade / Embalagem')
                            ->placeholder('Ex.: Caixa 12un, Fardo 6kg')
                            ->maxLength(120),

                        TextInput::make('ordem')
                            ->label('Ordem de exibição')
                            ->numeric()
                            ->default(0),

                        Textarea::make('descricao')
                            ->label('Descrição')
                            ->rows(3)
                            ->columnSpanFull(),

                        FileUpload::make('imagem')
                            ->label('Imagem')
                            ->image()
                            ->disk('public')
                            ->directory('produtos')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),

                Section::make('Publicação')
                    ->columns(2)
                    ->components([
                        Toggle::make('ativo')
                            ->label('Ativo (visível no site)')
                            ->default(true),

                        Toggle::make('destaque')
                            ->label('Destaque')
                            ->helperText('Aparece primeiro na listagem.')
                            ->default(false),
                    ]),
            ]);
    }
}
