<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('suppliers')->insert([
            [
                'id' => 1,
                'name' => 'Supplier A',
                'branch_id' => '001',
                'storage' => 'MAIN',
                'contact_person' => 'John Doe',
                'phone' => '08123456789',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Supplier B',
                'branch_id' => '001',
                'storage' => 'MAIN',
                'contact_person' => 'Jane Smith',
                'phone' => '08987654321',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
