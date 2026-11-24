<?php

namespace App\Services;

use App\Mail\OrderCreatedMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NotificationService
{
    public function __construct(private readonly CompanySettings $settings) {}

    public function orderCreated(Order $order): void
    {
        $order->loadMissing('items');
        $company = $this->settings->all();

        $this->sendOrderEmail($order, $order->customer_email_snapshot, 'customer', $company);
        $this->sendOrderEmail($order, (string) config('novastra.admin.email'), 'admin', $company);
    }

    /**
     * @param  array<string, string>  $company
     */
    private function sendOrderEmail(Order $order, string $email, string $recipient, array $company): void
    {
        try {
            Mail::to($email)->send(new OrderCreatedMail($order, $company, $recipient));
        } catch (Throwable $error) {
            Log::warning('Order email failed', [
                'order_id' => $order->id,
                'recipient' => $recipient,
                'error' => $error->getMessage(),
            ]);
        }
    }
}
