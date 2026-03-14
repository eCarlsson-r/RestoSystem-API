<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrepareSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('prepares')->insert([
            [
                'id' => 1,
                'name' => 'Chili Sauce',
                'cost' => 5000,
                'qty' => 10,
                'unit' => 'liter',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Chicken Stock',
                'cost' => 3000,
                'qty' => 20,
                'unit' => 'liter',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
