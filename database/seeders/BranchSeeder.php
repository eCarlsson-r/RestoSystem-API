<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('branches')->insert([
            [
                'id' => 1, // Mapping DMBRC to ID 1 for simplicity in FKs
                'name' => 'Demo Branch',
                'address' => 'Somewhere',
                'phone' => '66449374',
                'floor_number' => 1,
                'kitchen_no' => 1,
                'bartender_no' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
