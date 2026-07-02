<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetAvailableTables implements Tool
{
    public function __construct(private int $branchId) {}

    public function description(): string
    {
        return 'Fetch currently available tables at the branch. Use this to check table availability for walk-ins or reservation planning.';
    }

    public function handle(Request $request): string
    {
        $tables = DB::table('tables')
            ->where('branch_id', $this->branchId)
            ->where('status', 'available')
            ->select(['table_number', 'floor_number', 'capacity'])
            ->orderBy('table_number')
            ->get();

        if ($tables->isEmpty()) {
            return "No tables currently available.";
        }

        return "Available tables:\n" . $tables->map(fn($t) =>
            "Table {$t->table_number} (Floor {$t->floor_number}, Capacity: {$t->capacity})"
        )->implode("\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}