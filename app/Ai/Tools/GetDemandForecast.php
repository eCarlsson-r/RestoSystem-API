<?php

namespace App\Ai\Tools;

use App\Services\BigQueryAnalytics;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetDemandForecast implements Tool
{
    public function description(): string
    {
        return 'Fetch the ML demand forecast for the next 14 days: predicted transactions per day with a confidence range. Use for questions about expected busyness, staffing or stock planning.';
    }

    public function handle(Request $request): string
    {
        $rows = resolve(BigQueryAnalytics::class)->demandForecast();

        if (empty($rows)) {
            return 'No demand forecast available.';
        }

        return collect($rows)->map(fn ($f) =>
            "{$f['forecast_date']} ({$f['day_of_week']}) | predicted tx: " . round((float) $f['predicted_transactions'], 1) .
            ' | range: ' . round((float) $f['forecast_low'], 1) . '–' . round((float) $f['forecast_high'], 1) .
            " | confidence: {$f['confidence_pct']}%"
        )->implode("\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
