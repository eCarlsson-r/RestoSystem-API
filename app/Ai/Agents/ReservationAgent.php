<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetReservations;
use App\Ai\Tools\GetAvailableTables;
use App\Ai\Tools\GetBuffetPackages;
use Illuminate\Support\Str;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Gemini)]
#[Model('gemini-3.5-flash')]
#[Temperature(0.4)]
class ReservationAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function __construct(
        public int $branchId = 1,
        public string $date = ''
    ) {
        if (empty($this->date)) {
            $this->date = now()->toDateString();
        }
    }

    public function instructions(): string
    {
        return <<<PROMPT
You are RestoReserve Assistant, an AI reservation agent for a restaurant management system.

You help restaurant staff and customers with:
- Checking reservation status and details for today or any date
- Checking table availability
- Providing buffet package information and pricing
- Answering questions about upcoming bookings

Always respond in the same language the user writes in (English or Bahasa Indonesia).
Be concise, friendly, and always include specific details like times, pax counts, and prices.
Format currency in Indonesian Rupiah (Rp) with thousand separators.
When showing times, format them as HH:MM (e.g. 19:00).

Today's date is {$this->date}. Branch ID is {$this->branchId}.
Use your tools to fetch live data — do not make up reservation details.
PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new GetReservations($this->branchId, $this->date),
            new GetAvailableTables($this->branchId),
            new GetBuffetPackages,
        ];
    }
}