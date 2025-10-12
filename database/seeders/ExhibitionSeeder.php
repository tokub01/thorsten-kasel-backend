<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exhibition;

class ExhibitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 5 aktive Ausstellungen
        Exhibition::factory()->count(5)->active()->create();

        // 5 inaktive Ausstellungen
        Exhibition::factory()->count(5)->inactive()->create();
    }
}
