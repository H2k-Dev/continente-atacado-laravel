<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuário administrador do painel Filament
        User::updateOrCreate(
            ['email' => 'admin@continente-atacado.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ],
        );

        // Catálogo de exemplo (categoria => produtos)
        $catalogo = [
            'Bebidas' => [
                ['nome' => 'Refrigerante Cola 2L', 'unidade' => 'Fardo 6un', 'marca' => 'Coca-Cola'],
                ['nome' => 'Água Mineral 500ml', 'unidade' => 'Fardo 12un', 'marca' => 'Crystal'],
                ['nome' => 'Suco de Laranja 1L', 'unidade' => 'Caixa 12un', 'marca' => 'Del Valle'],
                ['nome' => 'Cerveja Pilsen Lata 350ml', 'unidade' => 'Fardo 12un', 'marca' => 'Brahma'],
            ],
            'Mercearia' => [
                ['nome' => 'Arroz Branco Tipo 1 5kg', 'unidade' => 'Fardo 6un', 'marca' => 'Tio João'],
                ['nome' => 'Feijão Carioca 1kg', 'unidade' => 'Fardo 10un', 'marca' => 'Camil'],
                ['nome' => 'Óleo de Soja 900ml', 'unidade' => 'Caixa 20un', 'marca' => 'Liza'],
                ['nome' => 'Açúcar Refinado 1kg', 'unidade' => 'Fardo 10un', 'marca' => 'União'],
                ['nome' => 'Macarrão Espaguete 500g', 'unidade' => 'Fardo 20un', 'marca' => 'Renata'],
            ],
            'Limpeza' => [
                ['nome' => 'Detergente Neutro 500ml', 'unidade' => 'Caixa 24un', 'marca' => 'Ypê'],
                ['nome' => 'Água Sanitária 2L', 'unidade' => 'Caixa 6un', 'marca' => 'Qboa'],
                ['nome' => 'Sabão em Pó 1kg', 'unidade' => 'Caixa 12un', 'marca' => 'Omo'],
            ],
            'Higiene' => [
                ['nome' => 'Papel Higiênico Folha Dupla', 'unidade' => 'Fardo 64 rolos', 'marca' => 'Neve'],
                ['nome' => 'Sabonete em Barra 90g', 'unidade' => 'Caixa 12un', 'marca' => 'Dove'],
            ],
            'Frios e Laticínios' => [
                ['nome' => 'Queijo Mussarela Peça', 'unidade' => 'Peça ~4kg', 'marca' => 'Tirolez'],
                ['nome' => 'Presunto Cozido', 'unidade' => 'Peça ~3kg', 'marca' => 'Sadia'],
                ['nome' => 'Leite Integral 1L', 'unidade' => 'Caixa 12un', 'marca' => 'Piracanjuba'],
            ],
        ];

        $ordemCat = 0;
        foreach ($catalogo as $nomeCategoria => $produtos) {
            $categoria = Category::updateOrCreate(
                ['slug' => Str::slug($nomeCategoria)],
                [
                    'nome' => $nomeCategoria,
                    'descricao' => "Produtos de {$nomeCategoria} para o seu negócio.",
                    'ordem' => $ordemCat++,
                    'ativo' => true,
                ],
            );

            $ordemProd = 0;
            foreach ($produtos as $produto) {
                Product::updateOrCreate(
                    ['slug' => Str::slug($produto['nome'])],
                    [
                        'category_id' => $categoria->id,
                        'nome' => $produto['nome'],
                        'unidade' => $produto['unidade'],
                        'marca' => $produto['marca'],
                        'descricao' => "{$produto['nome']} — {$produto['marca']}. Ideal para revenda e food service.",
                        'ativo' => true,
                        'destaque' => $ordemProd === 0,
                        'ordem' => $ordemProd++,
                    ],
                );
            }
        }
    }
}
