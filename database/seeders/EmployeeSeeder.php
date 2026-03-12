<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('employees')->insert([
            [
                'id' => 1,
                'name' => 'Demo Waitress',
                'branch_id' => 1, // DMBRC
                'gender' => 'F',
                'status' => 'J',
                'job_type' => 'SERVICE-WT',
                'join_date' => '2024-01-01',
                'quit_date' => null,
                'home_address' => '',
                'phone' => '0',
                'mobile' => '',
                'email' => 'waitress@demo.com',
                'account_id' => 2, // demo.waiter
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Demo Cashier',
                'branch_id' => 1, // DMBRC
                'gender' => 'F',
                'status' => 'J',
                'job_type' => 'SERVICE-CH',
                'join_date' => '2024-01-01',
                'quit_date' => null,
                'home_address' => '',
                'phone' => '0',
                'mobile' => '',
                'email' => 'cashier@demo.com',
                'account_id' => 3, // demo.cashier
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
