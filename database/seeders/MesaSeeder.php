<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mesa;

class MesaSeeder extends Seeder
{
    public function run(): void
    {
        $mesas = [
            ['numero' => 1, 'color' => 'Rojo'],
            ['numero' => 2, 'color' => 'Azul'],
            ['numero' => 3, 'color' => 'Verde'],
            ['numero' => 4, 'color' => 'Amarillo'],
            ['numero' => 5, 'color' => 'Morado'],
            ['numero' => 6, 'color' => 'Naranja'],
            ['numero' => 7, 'color' => 'Celeste'],
            ['numero' => 8, 'color' => 'Rosa'],
            ['numero' => 9, 'color' => 'Gris'],
            ['numero' => 10, 'color' => 'Dorado'],
            ['numero' => 11, 'color' => 'Turquesa'],
            ['numero' => 12, 'color' => 'Lila'],
            ['numero' => 13, 'color' => 'Marrón'],
            ['numero' => 14, 'color' => 'Beige'],
        ];

        foreach ($mesas as $mesa) {
            Mesa::updateOrCreate(
                ['numero' => $mesa['numero']],
                ['color' => $mesa['color']]
            );
        }
    }
}