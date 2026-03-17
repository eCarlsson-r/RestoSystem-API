<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        // Recipe for Nasi Putih (Product ID 1)
        DB::table('recipes')->insert([
            [
                'product_id' => '1',
                'item_type' => 'INGR',
                'item_code' => '1', // Salt
                'quantity' => 0.01,
                'unit' => 'g',
                'purchase_price' => 500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Prepare Recipe for Chili Sauce (Prepare ID 1)
        DB::table('prepare_recipes')->insert([
            [
                'prepare_id' => '1',
                'item_type' => 'INGR',
                'item_code' => '4', // Garlic
                'quantity' => 1,
                'unit' => 'g',
                'purchase_price' => 2000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
