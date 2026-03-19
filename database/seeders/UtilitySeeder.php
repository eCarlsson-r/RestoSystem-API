<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UtilitySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('utilities')->insert([
            [
                'id' => 1,
                'name' => 'Dining Spoon',
                'unit' => 'PCS'
            ],
            [
                'id' => 2,
                'name' => 'Plate',
                'unit' => 'PCS',
            ],
            [
                'id' => 3,  
                'name' => 'Dining Fork',
                'unit' => 'PCS',
            ],
        ]);
    }
}
