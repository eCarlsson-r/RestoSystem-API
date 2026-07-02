<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetBuffetPackages implements Tool
{
    public function description(): string
    {
        return 'Fetch active buffet packages with pricing. Use this to answer questions about buffet options, prices, and duration.';
    }

    public function handle(Request $request): string
    {
        $buffets = DB::table('buffets')
            ->where('is_active', 1)
            ->select(['name', 'price_adult', 'price_child', 'duration_minutes', 'description'])
            ->get();

        return "Active buffet packages:\n" . $buffets->map(fn($b) =>
            "{$b->name}: Adult Rp" . number_format($b->price_adult) .
            " | Child Rp" . number_format($b->price_child) .
            " | {$b->duration_minutes} minutes | {$b->description}"
        )->implode("\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}