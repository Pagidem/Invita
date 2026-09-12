<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Guest;

class GuestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Guest::factory()->count(50)->create([
            'confirmacion' => fake()->randomElement(['pendiente', 'confirmado', 'cancelado']),
            'companions' => fake()->numberBetween(0, 3),
        ]);
    }
}
