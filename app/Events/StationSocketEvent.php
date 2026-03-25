<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class StationSocketEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(
        public string $channel,
        public array $data
    ) {
        \Illuminate\Support\Facades\Log::info("StationSocketEvent created matching channel: " . $channel);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel($this->channel),
        ];
    }

    public function broadcastAs()
    {
        return 'notification.received';
    }

    public function broadcastWith()
    {
        return $this->data;
    }
}
