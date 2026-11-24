<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, string>  $company
     */
    public function __construct(
        public Order $order,
        public array $company,
        public string $recipient,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->recipient === 'admin'
            ? "Pesanan baru {$this->order->order_number}"
            : "Pesanan {$this->order->order_number} berhasil diterima";

        return new Envelope(subject: $subject.' · '.$this->company['company_name']);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.orders.created');
    }
}
