<?php

namespace Tests\Feature\Services;

use App\Mail\OrderCreatedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_order_created_sends_professional_role_specific_emails(): void
    {
        Mail::fake();
        config(['novastra.admin.email' => 'owner@example.com']);

        $order = Order::factory()->create([
            'order_number' => 'NVS-MAIL-001',
            'customer_name_snapshot' => 'Ayu Pelanggan',
            'customer_email_snapshot' => 'ayu@example.com',
            'address_snapshot' => 'Jalan Mawar 10, Tangerang',
            'phone_snapshot' => '081234567890',
            'subtotal' => 100000,
            'shipping_total' => 25000,
            'total' => 125000,
            'notes' => '<script>jangan dijalankan</script>',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'Ayam Segar',
            'product_sku' => 'AYM-001',
            'unit_price' => 50000,
            'quantity' => 2,
            'line_total' => 100000,
        ]);

        app(NotificationService::class)->orderCreated($order);

        Mail::assertSentCount(2);

        $customerMail = null;
        Mail::assertSent(OrderCreatedMail::class, function (OrderCreatedMail $mail) use (&$customerMail): bool {
            if ($mail->recipient !== 'customer') {
                return false;
            }

            $customerMail = $mail;

            return $mail->hasTo('ayu@example.com');
        });

        $adminMail = null;
        Mail::assertSent(OrderCreatedMail::class, function (OrderCreatedMail $mail) use (&$adminMail): bool {
            if ($mail->recipient !== 'admin') {
                return false;
            }

            $adminMail = $mail;

            return $mail->hasTo('owner@example.com');
        });

        $this->assertInstanceOf(OrderCreatedMail::class, $customerMail);
        $this->assertInstanceOf(OrderCreatedMail::class, $adminMail);
        $this->assertSame('Pesanan NVS-MAIL-001 berhasil diterima · Novastra', $customerMail->envelope()->subject);
        $this->assertSame('Pesanan baru NVS-MAIL-001 · Novastra', $adminMail->envelope()->subject);

        $customerHtml = $customerMail->render();
        $this->assertStringContainsString('Pesanan Anda sudah kami terima.', $customerHtml);
        $this->assertStringContainsString('Lihat detail pesanan', $customerHtml);
        $this->assertStringContainsString('Ayam Segar', $customerHtml);
        $this->assertStringContainsString('Rp125.000', $customerHtml);
        $this->assertStringContainsString('&lt;script&gt;jangan dijalankan&lt;/script&gt;', $customerHtml);
        $this->assertStringNotContainsString('<script>jangan dijalankan</script>', $customerHtml);

        $adminHtml = $adminMail->render();
        $this->assertStringContainsString('Ada pesanan baru untuk diproses.', $adminHtml);
        $this->assertStringContainsString('Kelola pesanan', $adminHtml);
        $this->assertStringContainsString('Ayu Pelanggan', $adminHtml);
    }
}
