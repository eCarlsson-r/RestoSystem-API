<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaleInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sale_invoices')->insert([
            [
                'id' => 1,
                'sale_id' => 1,
                'pay_method' => 'CS', // Cash
                'pay_bank' => null,
                'pay_card' => null,
                'pay_amount' => 10000,
                'pay_change' => 4500, // Assuming total was 5500 after tax/discount
                'employee_id' => 3, // demo.cashier
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
