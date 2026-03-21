<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('reservations')->insert([
            [
                'id' => 1,
                'customer_id' => 1,
                'event_date' => now()->toDateString(),
                'event_time' => '19:00:00',
                'buffet_id' => 1,
                'branch_id' => 1,
                'employee_id' => 1,
                'guaranteed_pax' => 4,
                'deposit_amount' => 100000,
                'deposit_status' => 'paid',
                'status' => 'confirmed',
                'notes' => 'Near window please',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'customer_id' => 1,
                'event_date' => now()->addDay()->toDateString(),
                'event_time' => '12:00:00',
                'buffet_id' => 2,
                'branch_id' => 1,
                'employee_id' => 1,
                'guaranteed_pax' => 10,
                'deposit_amount' => 500000,
                'deposit_status' => 'paid',
                'status' => 'pending',
                'notes' => 'Birthday party',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
