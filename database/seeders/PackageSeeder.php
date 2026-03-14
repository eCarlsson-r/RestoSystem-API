<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('packages')->insert([
            [
                'id' => 1,
                'name' => 'Couple Package',
                'price' => 75000,
                'desc' => '2 Meals + 2 Drinks',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Family Package',
                'price' => 150000,
                'desc' => '4 Meals + 4 Drinks + 1 Snack',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
