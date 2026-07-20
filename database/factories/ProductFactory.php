<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $nome = Str::title($this->faker->unique()->words(3, true));
        $unidades = ['Caixa 12un', 'Fardo 6kg', 'Pacote 500g', 'Saco 25kg', 'Caixa 24un', 'Unidade'];

        return [
            'category_id' => Category::factory(),
            'nome' => $nome,
            'slug' => Str::slug($nome) . '-' . $this->faker->unique()->numberBetween(1, 99999),
            'descricao' => $this->faker->sentence(12),
            'unidade' => $this->faker->randomElement($unidades),
            'marca' => $this->faker->randomElement(['Sadia', 'Nestlé', 'Tio João', 'Coca-Cola', 'Ypê', 'Piracanjuba', null]),
            'ativo' => true,
            'destaque' => $this->faker->boolean(15),
            'ordem' => $this->faker->numberBetween(0, 20),
        ];
    }
}
