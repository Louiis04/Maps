<?php

namespace Database\Seeders;

use App\Models\Map;
use Illuminate\Database\Seeder;

class MapSeeder extends Seeder
{

    public function run()
    {
        Map::factory()
            ->count(10)
            ->withRandomCity()
            ->create();
            
        $this->command->info('10 mapas de cidades foram criados com sucesso!');
    }
}