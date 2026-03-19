<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('stocks')->insert([
            [
                'item_type' => 'INGR',
                'item_code' => '1', // Salt
                'branch_id' => '001',
                'storage' => 'MAIN',
                'purchase_price' => 5000,
                'quantity' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_type' => 'INGR',
                'item_code' => '2', // Sugar
                'branch_id' => '001',
                'storage' => 'MAIN',
                'purchase_price' => 12000,
                'quantity' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'item_type' => 'PREP',
                'item_code' => '1', // Chili Sauce
                'branch_id' => '001',
                'storage' => 'MAIN',
                'purchase_price' => 5000,
                'quantity' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
