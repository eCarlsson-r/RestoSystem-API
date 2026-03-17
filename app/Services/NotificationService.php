<?php

namespace App\Services;

use App\Models\User;
use App\Events\OrderReady;
use App\Notifications\WebPushNotification;

class NotificationService
{
    public function notifyOrderReady($sale) {
        // 1. Live UI Update (Reverb) - Fast, for active waiters
        broadcast(new OrderReady($sale))->toOthers();

        // 2. Push Notification (WebPush) - For waiters with phones in pockets
        $waiter = $sale->user; 
        $waiter->notify(new WebPushNotification(
            title: "Table {$sale->table_number} Ready!",
            body: "Pick up order for {$sale->customer_name}"
        ));
    }

    public function notifyAdminNewSale($sale) {
        // Triggered on checkout
        $admin = User::where('type', 'admin')->first();
        if ($admin) {
            $admin->notify(new WebPushNotification(
                title: "💰 New Sale: Rp " . number_format($sale->total),
                body: "Table {$sale->table_number} has settled their bill."
            ));
        }
    }

    public function notifyWaiterCalled($table, $branchId) {
        $staff = User::whereIn('type', ['waiter', 'cashier'])
            ->whereHas('employee', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })->get();

        foreach ($staff as $user) {
            $user->notify(new WebPushNotification(
                title: "Table {$table} called!",
                body: "Table {$table} has been called."
            ));
        }
    }

    public function notifyProductStatusChanged($productId, $soldout) {
        // Notify Admin
        $admins = User::where('type', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new WebPushNotification(
                title: "Product {$productId} status changed!",
                body: "Product {$productId} has been changed to " . ($soldout ? 'Sold Out' : 'Available')
            ));
        }
        
        // Notify all waiters and cashiers (since products are currently global)
        $staff = User::whereIn('type', ['waiter', 'cashier'])->get();
        foreach ($staff as $user) {
             $user->notify(new WebPushNotification(
                title: "Product " . ($soldout ? 'Sold Out' : 'Available'),
                body: "Product ID {$productId} is now " . ($soldout ? 'unavailable' : 'available')
            ));
        }
    }
}