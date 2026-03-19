<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Demo Admin',
                'email' => 'admin@demo.com',
                'username' => 'demo.admin',
                'password' => Hash::make('Am12345'),
                'type' => 'ADMIN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Demo Waitress',
                'email' => 'waitress@demo.com',
                'username' => 'demo.waiter',
                'password' => Hash::make('Wt12345'),
                'type' => 'WAITER',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Demo Cashier',
                'email' => 'cashier@demo.com',
                'username' => 'demo.cashier',
                'password' => Hash::make('Cs12345'),
                'type' => 'CASHIER',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Demo Kitchen',
                'email' => 'kitchen@demo.com',
                'username' => 'demo.kitchen',
                'password' => Hash::make('Kt12345'),
                'type' => 'KITCHEN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Demo Bartender',
                'email' => 'bartender@demo.com',
                'username' => 'demo.bartender',
                'password' => Hash::make('Bt12345'),
                'type' => 'BARTENDER',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
