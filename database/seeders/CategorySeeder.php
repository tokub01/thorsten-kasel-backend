<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::factory()->create([
            'name' => 'Öl'
        ]);
        Category::factory()->create([
            'name' => 'Acryl'
        ]);
        Category::factory()->create([
            'name' => 'Zeichnungen'
        ]);
        Category::factory()->create([
            'name' => 'Skulpturen'
        ]);
    }
}
