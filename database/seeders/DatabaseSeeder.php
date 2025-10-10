<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        User::factory([
           'name' => 'Thorsten Kasel',
           'email' => 'thorsten.kasel@web.de',
           'password' => 'nfT0CDYjG9p^k0&V^qLFQW9%T^vRkT'
        ])->create();
    }
}
