<?php

namespace App\Http\Controllers;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnalyticsController
{
    public function menuPerformance()
    {
        return $this->view('v_menu_performance', orderBy: 'total_revenue DESC');
    }

    public function dailySales(Request $request)
    {
        $days = min((int) $request->input('days', 90), 365);

        return $this->view(
            'v_daily_sales',
            where: "sale_date >= DATE_SUB(CURRENT_DATE(), INTERVAL {$days} DAY)",
            orderBy: 'sale_date ASC',
            cacheSuffix: "days:{$days}",
        );
    }

    public function stockLevels()
    {
        return $this->view('v_stock_levels', orderBy: 'stock_value DESC');
    }

    public function reservations()
    {
        // event_time is a BigQuery INTERVAL, which the PHP client cannot map — cast it server-side.
        return $this->view(
            'v_reservations',
            select: '* EXCEPT(event_time), CAST(event_time AS STRING) AS event_time',
            orderBy: 'event_date DESC',
            limit: 200,
        );
    }

    public function menuClusters()
    {
        return $this->view('v_menu_clusters', orderBy: 'cluster_id ASC, total_revenue DESC');
    }

    public function demandForecast()
    {
        return $this->view('v_demand_forecast', orderBy: 'forecast_date ASC');
    }

    /** Runs a query against an allowlisted BigQuery view, cached to limit per-query billing. */
    private function view(string $view, ?string $where = null, ?string $orderBy = null, ?int $limit = null, string $cacheSuffix = '', string $select = '*')
    {
        $config = config('services.bigquery');

        $rows = Cache::remember(
            "bq:{$view}" . ($cacheSuffix ? ":{$cacheSuffix}" : ''),
            (int) $config['cache_ttl'],
            function () use ($view, $where, $orderBy, $limit, $config, $select) {
                $options = ['projectId' => $config['project_id']];

                // Prefer inline credentials (secret env vars) so production never
                // needs the key file on disk; fall back to a key file path.
                // Base64 survives env-file parsers that choke on raw JSON.
                if ($config['credentials_base64']) {
                    $options['keyFile'] = json_decode(base64_decode($config['credentials_base64']), true);
                } elseif ($config['credentials_json']) {
                    $options['keyFile'] = json_decode($config['credentials_json'], true);
                } elseif ($credentials = $config['credentials']) {
                    if (! str_starts_with($credentials, '/')) {
                        $credentials = base_path($credentials);
                    }
                    $options['keyFilePath'] = $credentials;
                }

                $bigQuery = new BigQueryClient($options);

                $sql = "SELECT {$select} FROM `{$config['project_id']}.{$config['dataset']}.{$view}`";
                if ($where) {
                    $sql .= " WHERE {$where}";
                }
                if ($orderBy) {
                    $sql .= " ORDER BY {$orderBy}";
                }
                if ($limit) {
                    $sql .= " LIMIT {$limit}";
                }

                $results = $bigQuery->runQuery($bigQuery->query($sql));

                return collect($results->rows())
                    ->map(fn ($row) => collect($row)->map(fn ($value) => $this->scalar($value))->all())
                    ->values()
                    ->all();
            }
        );

        return response()->json(['data' => $rows]);
    }

    /** BigQuery returns typed objects (Date, Numeric, Interval, ...) — flatten to JSON-friendly scalars. */
    private function scalar(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        if (is_object($value)) {
            return (string) $value;
        }

        return $value;
    }
}
