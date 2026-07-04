<?php

namespace App\Ai\Tools;

use App\Services\BigQueryAnalytics;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetStockLevels implements Tool
{
    public function description(): string
    {
        return 'Fetch current stock levels per item and storage location, including quantity and stock value. Use for questions about inventory, what is running low, or stock value.';
    }

    public function handle(Request $request): string
    {
        $rows = resolve(BigQueryAnalytics::class)->stockLevels();

        if (empty($rows)) {
            return 'No stock data available.';
        }

        return collect($rows)->map(fn ($s) =>
            "{$s['item_name']} ({$s['item_type']}) | {$s['branch_name']} / {$s['storage']} | " .
            "qty: {$s['quantity']} | value: Rp " . number_format((int) $s['stock_value'])
        )->implode("\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
