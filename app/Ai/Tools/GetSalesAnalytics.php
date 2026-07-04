<?php

namespace App\Ai\Tools;

use App\Services\BigQueryAnalytics;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetSalesAnalytics implements Tool
{
    public function description(): string
    {
        return 'Fetch daily sales analytics: transactions per day, buffet vs alacarte sessions, pax counts and revenue. Use for questions about sales performance, revenue, busy days or trends.';
    }

    public function handle(Request $request): string
    {
        $days = min((int) ($request['days'] ?? 30), 365);

        $rows = resolve(BigQueryAnalytics::class)->dailySales($days);

        if (empty($rows)) {
            return "No sales data found for the last {$days} days.";
        }

        $totalRevenue = 0;
        $totalTx = 0;
        foreach ($rows as $row) {
            $totalRevenue += (float) $row['buffet_revenue'] + (float) $row['alacarte_revenue'];
            $totalTx += (int) $row['total_transactions'];
        }

        $daily = collect($rows)->map(fn ($r) =>
            "{$r['sale_date']} | tx: {$r['total_transactions']} | buffet sessions: {$r['buffet_sessions']} | " .
            "alacarte: {$r['alacarte_sessions']} | revenue: Rp " . number_format((float) $r['buffet_revenue'] + (float) $r['alacarte_revenue'])
        )->implode("\n");

        return "Summary last {$days} days: total revenue Rp " . number_format($totalRevenue) .
            ", total transactions {$totalTx}, avg daily revenue Rp " . number_format($totalRevenue / max(count($rows), 1)) .
            "\n\nDaily breakdown:\n{$daily}";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'days' => $schema->integer()
                ->description('How many past days of sales to fetch (default 30, max 365).'),
        ];
    }
}
