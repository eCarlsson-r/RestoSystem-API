<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'id' => 1, // NO -> 1
                'name' => 'Mie',
                'slug' => 'mie',
                'icon_name' => 'utensils',
                'kitchen_process' => 'KTCN',
                'description' => 'Aneka ragam hidangan olahan mie',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Nasi',
                'slug' => 'nasi',
                'icon_name' => 'utensils',
                'kitchen_process' => 'KTCN',
                'description' => 'Aneka hidangan olahan nasi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Daging',
                'slug' => 'daging',
                'icon_name' => 'beef',
                'kitchen_process' => 'KTCN',
                'description' => 'Berbagai pilihan daging grill dan shabu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Minuman',
                'slug' => 'minuman',
                'icon_name' => 'cup-soda',
                'kitchen_process' => 'BAR',
                'description' => 'Minuman dingin dan panas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Snack',
                'slug' => 'snack',
                'icon_name' => 'popcorn',
                'kitchen_process' => 'KTCN',
                'description' => 'Camilan dan hidangan penutup',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
