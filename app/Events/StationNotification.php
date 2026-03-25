<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Models\User;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class StationNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public function __construct(
        public string $channel, // e.g., 'admin', 'kitchen', 'branch.1'
        public array $data
    ) {}

    public function via($notifiable)
    {
        return ['broadcast', WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title($this->data['title'] ?? 'Notification')
            ->body($this->data['body'] ?? '')
            ->data(['type' => $this->data['type'] ?? '']);
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->data);
    }
    
    public function broadcastWith()
    {
        return $this->data;
    }

    public function broadcastOn()
    {
        return new Channel($this->channel);
    }

    public function broadcastAs()
    {
        return 'notification.received';
    }

    public function broadcastType()
    {
        return 'notification.received';
    }

    public static function notifySubscribers(string $channel, array $data)
    {
        broadcast(new StationSocketEvent($channel, $data));
        
        $notification = new self($channel, $data);
        
        $query = User::query();
        if ($channel === 'admin') {
            $query->where('type', 'ADMIN');
        } elseif ($channel === 'kitchen') {
            $query->where('type', 'KITCHEN');
        } elseif (str_starts_with($channel, 'branch.')) {
            $branchId = str_replace('branch.', '', $channel);
            $query->whereIn('type', ['WAITER', 'CASHIER'])
                  ->whereHas('employee', function($q) use ($branchId) {
                      $q->where('branch_id', $branchId);
                  });
        }
        
        $users = $query->get();
        NotificationFacade::send($users, $notification);
    }
}