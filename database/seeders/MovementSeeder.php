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
                'from_branch_id' => '001',
                'from_storage' => 'WARH',
                'to_branch_id' => '002',
                'to_storage' => 'SHOP',
                'date' => '2024-03-10',
                'time' => '10:00:00',
                'status' => 'DONE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('stock_movement_records')->insert([
            [
                'movement_id' => 1,
                'item_type' => 'INGR',
                'item_code' => '1', // Salt
                'qty' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
