<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        // Beispielhafte Bildtitel (nur zu Demonstrationszwecken)
        $paintings = [
            'Mona Lisa',
            'Das letzte Abendmahl',
            'Die Sternennacht',
            'Der Schrei',
            'Die Geburt der Venus',
            'Guernica',
            'Mädchen mit dem Perlenohrring',
            'Der Kuss',
            'Die Freiheit führt das Volk',
            'Der Garten der Lüste',
            'Nachtwache',
            'Selbstbildnis mit Dornenhalsband',
            'Wasserseerosen',
            'Der Wanderer über dem Nebelmeer',
            'Amerikanische Gotik',
            'Komposition VIII',
            'Die Beständigkeit der Erinnerung',
            'Das Floß der Medusa',
            'Das Frühstück im Grünen',
            'Sonntag im Park von La Grande Jatte',
        ];

        $title = fake()->randomElement($paintings);

        return [
            'title' => "Beispiel: {$title}", // Hinweis für Anwender
            'description' => "Dies ist ein Beispieltext für das Kunstwerk '{$title}'. Die Daten dienen nur Demonstrationszwecken.",
            'image' => 'products/placeholder.gif', // Platzhalter-Bild
            'category_id' => Category::inRandomOrder()->value('id') ?? Category::factory(),
        ];
    }
}
