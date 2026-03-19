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
                'description' => 'Table salt',
                'unit' => 'KG'
            ],
            [
                'id' => 2,
                'name' => 'Sugar',
                'description' => 'Granulated sugar',
                'unit' => 'KG'
            ],
            [
                'id' => 3,
                'name' => 'Cooking Oil',
                'description' => 'Vegetable oil',
                'unit' => 'LTR'
            ],
            [
                'id' => 4,
                'name' => 'Garlic',
                'description' => 'Fresh garlic',
                'unit' => 'KG'
            ],
            [
                'id' => 5,
                'name' => 'Onion',
                'description' => 'Red onion',
                'unit' => 'KG'
            ],
        ]);
    }
}
