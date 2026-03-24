<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuffetSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('buffets')->insert([
            [
                'id' => 1,
                'name' => 'Premium Buffet',
                'price_adult' => 150000,
                'price_child' => 75000,
                'duration_minutes' => 90,
                'is_active' => true,
                'description' => 'Unlimited meat and sides'
            ],
            [
                'id' => 2,
                'name' => 'Standard Buffet',
                'price_adult' => 99000,
                'price_child' => 49000,
                'duration_minutes' => 60,
                'is_active' => true,
                'description' => 'Basic variety of grilled meats'
            ],
        ]);

        // Link Items to Buffet
        $items = [];
        
        // Premium (ID 1) gets everything (ID 1-11)
        for ($i = 1; $i <= 11; $i++) {
            $items[] = [
                'buffet_id' => 1, 
                'product_id' => $i,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        // Standard (ID 2) gets everything except Wagyu (ID 5)
        for ($i = 1; $i <= 11; $i++) {
            if ($i == 5) continue;
            $items[] = [
                'buffet_id' => 2, 
                'product_id' => $i,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        DB::table('buffet_items')->insert($items);
        
        DB::table('branch_buffet')->insert([
            [
                'branch_id' => 1,
                'buffet_id' => 1
            ],
            [
                'branch_id' => 1,
                'buffet_id' => 2
            ]
        ]);
    }
}
