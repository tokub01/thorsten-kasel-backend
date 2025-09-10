<?php


namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->name(),
            'description' => fake()->unique()->text(),
            'image' => 'products/TDkOWw0ffNF2ZZ9FXvqOyCkSmMceaUAeuqdbRnr0.png',
            'category_id' => Category::factory()->create()->id,
        ];
    }
}
