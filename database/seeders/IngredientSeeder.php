<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ingredients')->insert([
            [
                'id' => 1,
                'name' => 'Salt',
                'desc' => 'Table salt',
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Sugar',
                'desc' => 'Granulated sugar',
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Cooking Oil',
                'desc' => 'Vegetable oil',
                'unit' => 'liter',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Garlic',
                'desc' => 'Fresh garlic',
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Onion',
                'desc' => 'Red onion',
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
