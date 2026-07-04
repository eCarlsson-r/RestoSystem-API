<?php

namespace App\Ai\Tools;

use App\Services\BigQueryAnalytics;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetMenuInsights implements Tool
{
    public function description(): string
    {
        return 'Fetch menu performance (revenue, quantity sold, margin, discount per product) and ML cluster labels (Premium Seller, Volume Staple, Over-Discounted, Beverage Star). Use for questions about best/worst sellers, product margins, discounts or menu strategy.';
    }

    public function handle(Request $request): string
    {
        $analytics = resolve(BigQueryAnalytics::class);

        $performance = collect($analytics->menuPerformance());
        $clusters = collect($analytics->menuClusters())->keyBy('product_name');

        if ($performance->isEmpty()) {
            return 'No menu performance data available.';
        }

        return $performance->map(function ($p) use ($clusters) {
            $cluster = $clusters[$p['product_name']]['cluster_label'] ?? 'Unclustered';

            return "{$p['product_name']} ({$p['category']}) | cluster: {$cluster} | " .
                'sold: ' . number_format((int) $p['total_qty_sold']) . 'x | ' .
                'revenue: Rp ' . number_format((int) $p['total_revenue']) . ' | ' .
                'margin: ' . round((float) $p['margin_pct'], 1) . '% | ' .
                'avg discount: ' . round((float) $p['avg_discount_pct'], 2) . '%';
        })->implode("\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
