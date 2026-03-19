<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovementSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('stock_movements')->insert([
            [
                'id' => 1,
                'from_branch_id' => 1,
                'from_storage' => 'MAIN',
                'to_branch_id' => 1,
                'to_storage' => 'KTCN',
                'date' => '2024-03-10',
                'time' => '10:00:00',
                'status' => 'R',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('stock_movement_records')->insert([
            [
                'movement_id' => 1,
                'item_type' => 'INGR',
                'item_code' => 1, // Salt
                'quantity' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
