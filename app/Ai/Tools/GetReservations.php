<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetReservations implements Tool
{
    public function __construct(
        private int $branchId,
        private string $defaultDate
    ) {}

    public function description(): string
    {
        return 'Fetch reservations for a specific date and branch. Use this to answer questions about bookings, guest counts, deposit status, and reservation details.';
    }

    public function handle(Request $request): string
    {
        $date = $request['date'] ?? $this->defaultDate;

        $reservations = DB::table('reservations as r')
            ->join('customers as c', 'r.customer_id', '=', 'c.id')
            ->join('branches as b', 'r.branch_id', '=', 'b.id')
            ->leftJoin('buffets as bf', 'r.buffet_id', '=', 'bf.id')
            ->where('r.branch_id', $this->branchId)
            ->where('r.event_date', $date)
            ->whereNull('r.deleted_at')
            ->whereNotIn('r.status', ['cancelled'])
            ->select([
                'r.id', 'c.name as customer', 'r.event_time',
                'r.guaranteed_pax', 'r.deposit_amount', 'r.deposit_status',
                'r.status', 'r.notes', 'bf.name as buffet',
            ])
            ->orderBy('r.event_time')
            ->get();

        if ($reservations->isEmpty()) {
            return "No reservations found for {$date}.";
        }

        return $reservations->map(fn($r) =>
            "ID#{$r->id} | {$r->customer} | {$r->event_time} | " .
            "Pax: {$r->guaranteed_pax} | Buffet: {$r->buffet} | " .
            "Deposit: {$r->deposit_status} | Status: {$r->status}" .
            ($r->notes ? " | Notes: {$r->notes}" : "")
        )->implode("\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'date' => $schema->string()
                ->description('Date to fetch reservations for in YYYY-MM-DD format. Defaults to today if not provided.')
        ];
    }
}