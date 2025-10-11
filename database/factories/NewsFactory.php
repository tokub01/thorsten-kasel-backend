<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsFactory extends Factory
{
    protected $model = News::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(),
            'text' => $this->faker->paragraphs(3, true),
            'image' => 'news/placeholder.gif', // oder eine URL falls gewünscht
            'isActive' => $this->faker->boolean(50), // zufällig aktiv/inaktiv
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Zustand: aktiv
     */
    public function active()
    {
        return $this->state(fn (array $attributes) => ['isActive' => true]);
    }

    /**
     * Zustand: inaktiv
     */
    public function inactive()
    {
        return $this->state(fn (array $attributes) => ['isActive' => false]);
    }
}
