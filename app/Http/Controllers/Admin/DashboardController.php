<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PageView;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const RANGE_LABELS = [
        '1m' => '1 bulan',
        '3m' => '3 bulan',
        '6m' => '6 bulan',
        '1y' => '1 tahun',
    ];

    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'range' => ['nullable', Rule::in(array_keys(self::RANGE_LABELS))],
        ]);
        $range = (string) ($validated['range'] ?? '1m');
        [$since, $bucketUnit] = match ($range) {
            '3m' => [now()->startOfWeek()->subWeeks(12), 'week'],
            '6m' => [now()->startOfMonth()->subMonths(5), 'month'],
            '1y' => [now()->startOfMonth()->subMonths(11), 'month'],
            default => [now()->subDays(29)->startOfDay(), 'day'],
        };
        $rangeLabel = self::RANGE_LABELS[$range];
        $orders = Order::where('created_at', '>=', $since);
        $revenue = (clone $orders)->where('payment_status', 'PAID')->sum('total');
        $orderCount = (clone $orders)->count();
        $paidOrderCount = (clone $orders)->where('payment_status', 'PAID')->count();
        $visitors = PageView::where('day', '>=', $since->toDateString())->distinct('visitor_id')->count('visitor_id');
        $dailyRevenue = Order::query()
            ->selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->where('payment_status', 'PAID')
            ->where('created_at', '>=', $since)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'day');
        $dailyVisitors = PageView::query()
            ->selectRaw('day, COUNT(DISTINCT visitor_id) as total')
            ->where('day', '>=', $since->toDateString())
            ->groupBy('day')
            ->pluck('total', 'day');
        $trend = $this->buildTrend($since, $bucketUnit, $dailyRevenue, $dailyVisitors);

        return view('admin.dashboard', [
            'metrics' => [
                "Omzet {$rangeLabel}" => $revenue,
                "Pesanan {$rangeLabel}" => $orderCount,
                'Rata-rata pesanan' => $paidOrderCount ? intdiv((int) $revenue, $paidOrderCount) : 0,
                'Pengunjung' => $visitors,
            ],
            'range' => $range,
            'rangeLabel' => $rangeLabel,
            'rangeOptions' => self::RANGE_LABELS,
            'trend' => $trend,
            'maximumRevenue' => max(1, (int) $trend->max('revenue')),
            'maximumVisitors' => max(1, (int) $trend->max('visitors')),
            'pending' => Order::where('created_at', '>=', $since)->where('status', 'PENDING')->count(),
            'unpaid' => Order::where('created_at', '>=', $since)->where('payment_status', 'UNPAID')->where('status', '!=', 'CANCELLED')->count(),
            'lowStock' => Product::with('category')->active()->where('stock', '<=', 5)->orderBy('stock')->limit(8)->get(),
            'topProducts' => Product::query()
                ->select(['products.id', 'products.name'])
                ->selectRaw('SUM(order_items.quantity) as ordered_quantity')
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('products.is_deleted', false)
                ->where('orders.status', '!=', 'CANCELLED')
                ->where('orders.created_at', '>=', $since)
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('ordered_quantity')
                ->limit(5)
                ->get(),
            'recentOrders' => Order::where('created_at', '>=', $since)->latest()->limit(6)->get(),
        ]);
    }

    /**
     * @param  Collection<string, int|string>  $dailyRevenue
     * @param  Collection<string, int|string>  $dailyVisitors
     * @return Collection<int, array{label: string, revenue: int, visitors: int}>
     */
    private function buildTrend(Carbon $since, string $bucketUnit, Collection $dailyRevenue, Collection $dailyVisitors): Collection
    {
        $revenueByBucket = $this->groupDailyValues($dailyRevenue, $bucketUnit);
        $visitorsByBucket = $this->groupDailyValues($dailyVisitors, $bucketUnit);
        $bucketStarts = collect();
        $cursor = $since->copy();

        while ($cursor->lte(now())) {
            $bucketStarts->push($cursor->copy());
            $cursor = match ($bucketUnit) {
                'week' => $cursor->addWeek(),
                'month' => $cursor->addMonth(),
                default => $cursor->addDay(),
            };
        }

        return $bucketStarts->map(function (Carbon $date) use ($bucketUnit, $revenueByBucket, $visitorsByBucket): array {
            $key = $this->bucketKey($date, $bucketUnit);

            return [
                'label' => $bucketUnit === 'month' ? $date->translatedFormat('M y') : $date->translatedFormat('d M'),
                'revenue' => (int) ($revenueByBucket[$key] ?? 0),
                'visitors' => (int) ($visitorsByBucket[$key] ?? 0),
            ];
        });
    }

    /**
     * @param  Collection<string, int|string>  $dailyValues
     * @return Collection<string, int>
     */
    private function groupDailyValues(Collection $dailyValues, string $bucketUnit): Collection
    {
        return $dailyValues->reduce(function (Collection $totals, int|string $value, string $day) use ($bucketUnit): Collection {
            $key = $this->bucketKey(Carbon::parse($day), $bucketUnit);
            $totals[$key] = (int) ($totals[$key] ?? 0) + (int) $value;

            return $totals;
        }, collect());
    }

    private function bucketKey(Carbon $date, string $bucketUnit): string
    {
        return match ($bucketUnit) {
            'week' => $date->startOfWeek()->toDateString(),
            'month' => $date->format('Y-m'),
            default => $date->toDateString(),
        };
    }
}
