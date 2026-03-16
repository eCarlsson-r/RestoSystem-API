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
                'employee_id' => 2, // demo.cashier
                'customer_id' => 1,
                'date' => '2024-03-03',
                'time' => '12:28:16',
                'discount' => 0,
                'tax' => 10,
                'status' => 'D',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'branch_id' => 1,
                'table_id' => 1,
                'employee_id' => 1, // demo.admin
                'customer_id' => 1,
                'date' => '2024-04-01',
                'time' => '21:49:29',
                'discount' => 0,
                'tax' => 10,
                'status' => 'O',
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
                'order_employee' => '3',
                'order_date' => '2024-03-02',
                'order_time' => '23:28:16',
                'deliver_employee' => '3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sale_id' => 4,
                'item_type' => 'product',
                'item_code' => 1,
                'quantity' => 2,
                'item_price' => 5000,
                'discount_pcnt' => 0,
                'discount_amnt' => 0,
                'item_note' => 'tambo',
                'item_status' => 'O',
                'order_employee' => '3',
                'order_date' => '2024-04-01',
                'order_time' => '14:38:02',
                'deliver_employee' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
