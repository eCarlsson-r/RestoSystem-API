<?php

namespace App\Http\Controllers;

use App\Services\BigQueryAnalytics;
use Illuminate\Http\Request;

class AnalyticsController
{
    public function __construct(
        private BigQueryAnalytics $analytics
    ) {}

    public function menuPerformance()
    {
        return response()->json(['data' => $this->analytics->menuPerformance()]);
    }

    public function dailySales(Request $request)
    {
        $days = (int) $request->input('days', 90);

        return response()->json(['data' => $this->analytics->dailySales($days)]);
    }

    public function stockLevels()
    {
        return response()->json(['data' => $this->analytics->stockLevels()]);
    }

    public function reservations()
    {
        return response()->json(['data' => $this->analytics->reservations()]);
    }

    public function menuClusters()
    {
        return response()->json(['data' => $this->analytics->menuClusters()]);
    }

    public function demandForecast()
    {
        return response()->json(['data' => $this->analytics->demandForecast()]);
    }
}
