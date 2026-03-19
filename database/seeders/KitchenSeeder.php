<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KitchenSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('kitchen_requests')->insert([
            [
                'id' => 1,
                'date' => '2024-03-12',
                'time' => '09:00:00',
                'from_branch_id' => 1,
                'from_storage' => 'KTCN',
                'to_branch_id' => 1,
                'to_storage' => 'MAIN',
                'status' => 'Q',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('kitchen_request_items')->insert([
            [
                'request_id' => 1,
                'item_type' => 'INGR',
                'item_code' => '1', // Salt
                'quantity' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
