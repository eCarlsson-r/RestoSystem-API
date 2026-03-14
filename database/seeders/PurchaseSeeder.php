<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        // Purchase Order
        DB::table('purchase_orders')->insert([
            [
                'id' => 1,
                'supplier_id' => 1,
                'date' => '2024-03-01',
                'delivery_date' => '2024-03-05',
                'description' => 'Initial stock purchase',
                'tax' => 10,
                'status' => 'P', // Pending/Paid? Let's assume P for Pending
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('purchase_order_records')->insert([
            [
                'purchase_order_id' => 1,
                'item_type' => 'INGR',
                'item_code' => '1', // Salt
                'quantity' => 10,
                'price' => 5000,
                'discount' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Purchase Return
        DB::table('purchase_returns')->insert([
            [
                'id' => 1,
                'supplier_id' => 1,
                'date' => '2024-03-06',
                'delivery_date' => '2024-03-07',
                'description' => 'Damaged goods return',
                'tax' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('purchase_return_records')->insert([
            [
                'purchase_return_id' => 1,
                'item_type' => 'INGR',
                'item_code' => '1', // Salt
                'quantity' => 2,
                'price' => 5000,
                'discount' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
