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
                'description' => '2 Meals + 2 Drinks'
            ],
            [
                'id' => 2,
                'name' => 'Family Package',
                'price' => 150000,
                'description' => '4 Meals + 4 Drinks + 1 Snack'
            ],
        ]);
    }
}
