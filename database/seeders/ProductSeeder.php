<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'code' => 'NS001',
                'name' => 'Nasi Putih',
                'desc' => '',
                'img_no' => 0,
                'category_id' => 2, // NS -> 2
                'price' => 5000,
                'cost' => 600,
                'discount' => 0,
                'soldout' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
