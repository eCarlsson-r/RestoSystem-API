<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('customers')->insert([
            [
                'id' => 1,
                'name' => 'Demo Customer',
                'gender' => 'M',
                'pob' => 'Meda',
                'dob' => '1975-08-23',
                'address' => '',
                'mobile' => '08357583908',
                'email' => '',
                'discount' => 0,
                'tax' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
