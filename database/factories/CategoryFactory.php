<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $nome = $this->faker->unique()->words(2, true);

        return [
            'nome' => Str::title($nome),
            'slug' => Str::slug($nome),
            'descricao' => $this->faker->sentence(),
            'ordem' => $this->faker->numberBetween(0, 20),
            'ativo' => true,
        ];
    }
}
