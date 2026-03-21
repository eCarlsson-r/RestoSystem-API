<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        // Sample Sales
        $sales = [
            [
                'id' => 1,
                'branch_id' => 1,
                'table_id' => 1,
                'employee_id' => 2, // cashier
                'customer_id' => 1,
                'buffet_id' => 1,
                'adult_count' => 2,
                'child_count' => 0,
                'adult_price' => 150000,
                'child_price' => 75000,
                'duration_minutes' => 90,
                'date' => '2024-03-03',
                'time' => '12:28:16',
                'discount' => 0,
                'tax' => 10,
                'status' => 'D',
                'reservation_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'branch_id' => 1,
                'table_id' => 2,
                'employee_id' => 2,
                'customer_id' => 1,
                'buffet_id' => null, // ALACARTE
                'adult_count' => null,
                'child_count' => null,
                'adult_price' => null,
                'child_price' => null,
                'duration_minutes' => null,
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
                'discount' => 0,
                'tax' => 10,
                'status' => 'O',
                'reservation_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'branch_id' => 1,
                'table_id' => 3,
                'employee_id' => 2,
                'customer_id' => 1,
                'buffet_id' => 2, // Standard
                'adult_count' => 4,
                'child_count' => 0,
                'adult_price' => 99000,
                'child_price' => 49000,
                'duration_minutes' => 60,
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
                'discount' => 0,
                'tax' => 10,
                'status' => 'O',
                'reservation_id' => 1, // LINK TO RESERVATION
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($sales as $sale) {
            DB::table('sales')->insert($sale);
        }

        // Sample Sale Records
        DB::table('sale_records')->insert([
            [
                'sale_id' => 1,
                'item_type' => 'product',
                'item_code' => 1,
                'quantity' => 3,
                'item_price' => 5000,
                'discount_pcnt' => 0,
                'discount_amnt' => 0,
                'item_note' => '',
                'item_status' => 'D',
                'order_employee' => '2',
                'order_date' => '2024-03-02',
                'order_time' => '23:28:16',
                'deliver_employee' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sale_id' => 2,
                'item_type' => 'product',
                'item_code' => 3, // Mie Goreng Jawa
                'quantity' => 2,
                'item_price' => 22000,
                'discount_pcnt' => 0,
                'discount_amnt' => 0,
                'item_note' => 'tambo',
                'item_status' => 'O',
                'order_employee' => '2',
                'order_date' => now()->toDateString(),
                'order_time' => now()->toTimeString(),
                'deliver_employee' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
