<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 5 aktive News
        News::factory()->count(5)->active()->create();

        // 5 inaktive News
        News::factory()->count(5)->inactive()->create();
    }
}
