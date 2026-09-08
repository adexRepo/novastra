<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NotificationService
{
    public function orderCreated(Order $order): void
    {
        try {
            Mail::raw("Pesanan {$order->order_number} berhasil dibuat. Total Rp ".number_format($order->total, 0, ',', '.').'.', function ($message) use ($order) {
                $message->to($order->customer_email_snapshot)->subject("Pesanan Novastra {$order->order_number}");
            });
        } catch (Throwable $error) {
            Log::warning('Order email failed', ['order_id' => $order->id, 'error' => $error->getMessage()]);
        }
    }
}
