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
                'kitchen_process' => 'KTCN',
                'description' => 'Aneka ragam hidangan olahan mie',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2, // NS -> 2
                'name' => 'Nasi',
                'kitchen_process' => 'KTCN',
                'description' => 'Aneka hidangan olahan nasi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
