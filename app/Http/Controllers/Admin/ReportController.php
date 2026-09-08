<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminOrderFilterRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports');
    }

    public function orders(AdminOrderFilterRequest $request): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Nomor Pesanan', 'Pelanggan', 'Email', 'Tanggal', 'Produk', 'Total', 'Status', 'Pembayaran'], null, 'A1');
        $row = 2;
        foreach (Order::with('items')->adminFilters($request->filters())->latest()->lazy(200) as $order) {
            $textValues = [
                'A' => $order->order_number,
                'B' => $order->customer_name_snapshot,
                'C' => $order->customer_email_snapshot,
                'D' => $order->created_at->format('Y-m-d H:i'),
                'E' => $order->items->map(fn ($item) => "{$item->product_name} x{$item->quantity}")->join(', '),
                'G' => $order->status,
                'H' => $order->payment_status,
            ];

            foreach ($textValues as $column => $value) {
                $sheet->setCellValueExplicit("{$column}{$row}", $value, DataType::TYPE_STRING);
            }

            $sheet->setCellValue("F{$row}", $order->total);
            $row++;
        }
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $this->download($spreadsheet, 'novastra-orders-'.now()->format('Y-m-d').'.xlsx');
    }

    public function products(): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Nama Produk', 'SKU', 'Kategori', 'Harga', 'Stok', 'Status', 'Unggulan', 'Dibuat'], null, 'A1');
        $row = 2;

        foreach (Product::with('category')->visible()->orderByDesc('status')->latest()->lazy(200) as $product) {
            $textValues = [
                'A' => $product->name,
                'B' => $product->sku,
                'C' => $product->category->name,
                'F' => $product->status,
                'G' => $product->featured ? 'Ya' : 'Tidak',
                'H' => $product->created_at->format('Y-m-d H:i'),
            ];

            foreach ($textValues as $column => $value) {
                $sheet->setCellValueExplicit("{$column}{$row}", $value, DataType::TYPE_STRING);
            }

            $sheet->setCellValue("D{$row}", $product->price);
            $sheet->setCellValue("E{$row}", $product->stock);
            $row++;
        }

        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $this->download($spreadsheet, 'novastra-products-'.now()->format('Y-m-d').'.xlsx');
    }

    private function download(Spreadsheet $spreadsheet, string $filename): BinaryFileResponse
    {
        $temporaryDirectory = (string) config('novastra.temp_upload_dir');
        File::ensureDirectoryExists($temporaryDirectory, 0755, true);
        $path = tempnam($temporaryDirectory, 'report-');

        if ($path === false) {
            throw new RuntimeException('Tidak dapat membuat file laporan sementara.');
        }

        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }
}
