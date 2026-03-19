<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class SaleConfirmed extends Notification
{
    public function __construct(public $sale) {}

    // 1. Define which channels to use
    public function via($notifiable)
    {
        // We send to the database for the history log,
        // and WebPush for the real-time browser alert.
        return ['database', 'broadcast', WebPushChannel::class];
    }

    // 2. Logic for the Admin's Phone/Browser Alert
    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('💰 New Sale: Rp ' . number_format($this->sale->total))
            ->body("Table {$this->sale->table_number} settled by {$this->sale->user->name}")
            ->icon('/icons/pos-logo.png')
            ->action('View Dashboard', 'view_dashboard')
            ->options(['TTL' => 1000]);
    }

    // 3. Logic for the Internal Database Log
    public function toArray($notifiable)
    {
        return [
            'sale_id' => $this->sale->id,
            'amount' => $this->sale->total,
            'table' => $this->sale->table_number
        ];
    }
}

?>