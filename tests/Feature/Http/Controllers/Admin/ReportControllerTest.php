<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_product_export_contains_all_visible_products(): void
    {
        $this->travelTo('2026-09-08 12:00:00');
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $visibleProduct = Product::factory()->create(['name' => 'Produk Terlihat']);
        Product::factory()->create(['name' => 'Produk Terhapus', 'is_deleted' => true]);

        $response = $this->actingAs($admin)->get(route('admin.reports.products'));

        $response->assertDownload('novastra-products-2026-09-08.xlsx');
        $path = $response->baseResponse->getFile()->getPathname();
        $spreadsheet = IOFactory::load($path);
        unlink($path);
        $sheet = $spreadsheet->getActiveSheet();
        $this->assertSame($visibleProduct->name, $sheet->getCell('A2')->getValue());
        $this->assertNull($sheet->getCell('A3')->getValue());
    }

    public function test_order_export_uses_active_filters(): void
    {
        $this->travelTo('2026-09-08 12:00:00');
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $paidOrder = Order::factory()->create(['order_number' => 'NVS-PAID-001', 'payment_status' => 'PAID']);
        Order::factory()->create(['order_number' => 'NVS-UNPAID-001', 'payment_status' => 'UNPAID']);

        $response = $this->actingAs($admin)->get(route('admin.reports.orders', ['payment' => 'PAID']));

        $response->assertDownload('novastra-orders-2026-09-08.xlsx');
        $path = $response->baseResponse->getFile()->getPathname();
        $spreadsheet = IOFactory::load($path);
        unlink($path);
        $sheet = $spreadsheet->getActiveSheet();
        $this->assertSame($paidOrder->order_number, $sheet->getCell('A2')->getValue());
        $this->assertNull($sheet->getCell('A3')->getValue());
    }

    public function test_order_export_without_filters_contains_all_orders(): void
    {
        $this->travelTo('2026-09-08 12:00:00');
        $admin = User::factory()->create(['role' => 'ADMIN']);
        Order::factory()->create(['order_number' => 'NVS-FIRST-001']);
        Order::factory()->create(['order_number' => 'NVS-SECOND-001']);

        $response = $this->actingAs($admin)->get(route('admin.reports.orders'));

        $response->assertDownload('novastra-orders-2026-09-08.xlsx');
        $path = $response->baseResponse->getFile()->getPathname();
        $spreadsheet = IOFactory::load($path);
        unlink($path);
        $exportedOrderNumbers = [
            $spreadsheet->getActiveSheet()->getCell('A2')->getValue(),
            $spreadsheet->getActiveSheet()->getCell('A3')->getValue(),
        ];
        sort($exportedOrderNumbers);

        $this->assertSame(['NVS-FIRST-001', 'NVS-SECOND-001'], $exportedOrderNumbers);
    }

    public function test_customer_cannot_download_admin_reports(): void
    {
        $customer = User::factory()->create(['role' => 'CUSTOMER']);

        $this->actingAs($customer)
            ->get(route('admin.reports.products'))
            ->assertForbidden();
    }
}
