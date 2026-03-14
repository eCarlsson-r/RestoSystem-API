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
                'name' => 'Electricity',
                'desc' => 'Monthly electricity bill',
                'unit' => 'kWh',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Water',
                'desc' => 'Monthly water bill',
                'unit' => 'm3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Gas',
                'desc' => 'LPG Gas 12kg',
                'unit' => 'tube',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
