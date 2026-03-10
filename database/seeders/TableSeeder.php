<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [
            ['1', 1, 1, 4, 5, 5, 'H', 'occupied'],
            ['10', 1, 1, 4, 105, 105, 'H', 'available'],
            ['2', 1, 1, 4, 105, 5, 'V', 'available'],
            ['3', 1, 1, 4, 205, 5, 'H', 'available'],
            ['4', 1, 1, 4, 306, 5, 'H', 'available'],
            ['5', 1, 1, 4, 405, 5, 'H', 'available'],
            ['6', 1, 1, 4, 505, 5, 'H', 'available'],
            ['7', 1, 1, 4, 605, 5, 'H', 'available'],
            ['8', 1, 1, 4, 705, 5, 'H', 'available'],
            ['9', 1, 1, 4, 5, 105, 'H', 'available'],
        ];

        foreach ($tables as $t) {
            DB::table('tables')->insert([
                'table_number' => $t[0],
                'floor_number' => $t[1],
                'branch_id' => $t[2],
                'capacity' => $t[3],
                'position_x' => $t[4],
                'position_y' => $t[5],
                'direction' => $t[6],
                'status' => $t[7],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
