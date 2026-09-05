import Link from 'next/link';
import {
  AlertTriangle,
  ArrowRight,
  Banknote,
  CircleCheck,
  PackageSearch,
  ReceiptText,
  ShoppingCart,
  Users,
} from 'lucide-react';
import {
  DashboardCharts,
  type DashboardPoint,
} from '@/components/admin/dashboard-charts';
import { Badge } from '@/components/ui/badge';
import {
  businessDayKey,
  evaluateBusinessHealth,
  lastBusinessDayKeys,
  percentage,
  percentageChange,
} from '@/lib/analytics/business-metrics';
import { formatRupiah } from '@/lib/catalog';
import { prisma } from '@/lib/db/prisma';
import { cn } from '@/lib/utils';

const dayMs = 86_400_000;

function formatPercent(value: number) {
  return `${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(value)}%`;
}

export default async function AdminDashboardPage() {
  const now = new Date();
  const currentStart = new Date(now.getTime() - 30 * dayMs);
  const previousStart = new Date(now.getTime() - 60 * dayMs);
  const todayKey = businessDayKey(now);

  const [
    totalProducts,
    activeProducts,
    lowStockProducts,
    ordersInSixtyDays,
    recentOrders,
    topProductRows,
    trafficByDay,
    uniqueVisitors,
  ] = await Promise.all([
    prisma.product.count(),
    prisma.product.count({ where: { status: 'ACTIVE' } }),
    prisma.product.findMany({
      where: { status: 'ACTIVE', stock: { lte: 5 } },
      orderBy: { stock: 'asc' },
      take: 5,
      select: { id: true, name: true, stock: true },
    }),
    prisma.order.findMany({
      where: { createdAt: { gte: previousStart } },
      select: {
        createdAt: true,
        paymentStatus: true,
        status: true,
        total: true,
      },
    }),
    prisma.order.findMany({ orderBy: { createdAt: 'desc' }, take: 5 }),
    prisma.orderItem.groupBy({
      by: ['productSku', 'productName'],
      where: {
        order: {
          paymentStatus: 'PAID',
          createdAt: { gte: currentStart },
        },
      },
      _sum: { lineTotal: true, quantity: true },
      orderBy: { _sum: { quantity: 'desc' } },
      take: 5,
    }),
    prisma.pageView.groupBy({
      by: ['day'],
      where: { createdAt: { gte: currentStart } },
      _count: { _all: true },
      orderBy: { day: 'asc' },
    }),
    prisma.pageView.findMany({
      where: { createdAt: { gte: currentStart } },
      distinct: ['visitorId'],
      select: { visitorId: true },
    }),
  ]);

  const currentOrders = ordersInSixtyDays.filter(
    (order) => order.createdAt >= currentStart,
  );
  const previousOrders = ordersInSixtyDays.filter(
    (order) => order.createdAt < currentStart,
  );
  const paidOrders = currentOrders.filter(
    (order) => order.paymentStatus === 'PAID',
  );
  const currentRevenue = paidOrders.reduce(
    (total, order) => total + order.total.toNumber(),
    0,
  );
  const previousRevenue = previousOrders
    .filter((order) => order.paymentStatus === 'PAID')
    .reduce((total, order) => total + order.total.toNumber(), 0);
  const revenueTrend = percentageChange(currentRevenue, previousRevenue);
  const cancelledOrders = currentOrders.filter(
    (order) => order.status === 'CANCELLED',
  ).length;
  const paidRate = percentage(paidOrders.length, currentOrders.length);
  const cancellationRate = percentage(cancelledOrders, currentOrders.length);
  const averageOrderValue = paidOrders.length
    ? currentRevenue / paidOrders.length
    : 0;
  const pageViews = trafficByDay.reduce(
    (total, row) => total + row._count._all,
    0,
  );
  const conversionRate = percentage(
    currentOrders.filter((order) => order.status !== 'CANCELLED').length,
    uniqueVisitors.length,
  );
  const ordersToday = currentOrders.filter(
    (order) => businessDayKey(order.createdAt) === todayKey,
  ).length;
  const health = evaluateBusinessHealth({
    cancellationRate,
    lowStockCount: lowStockProducts.length,
    paidOrders: paidOrders.length,
    paidRate,
    revenueTrend,
  });

  const daily = new Map<string, DashboardPoint>(
    lastBusinessDayKeys(30, now).map((day) => [
      day,
      {
        day,
        label: `${day.slice(8, 10)}/${day.slice(5, 7)}`,
        orders: 0,
        revenue: 0,
        visits: 0,
      },
    ]),
  );

  for (const order of currentOrders) {
    const point = daily.get(businessDayKey(order.createdAt));
    if (!point) continue;
    if (order.status !== 'CANCELLED') point.orders += 1;
    if (order.paymentStatus === 'PAID') {
      point.revenue += order.total.toNumber();
    }
  }
  for (const traffic of trafficByDay) {
    const point = daily.get(traffic.day);
    if (point) point.visits = traffic._count._all;
  }

  const topProducts = topProductRows.map((product) => ({
    name: product.productName,
    quantity: product._sum.quantity ?? 0,
    revenue: product._sum.lineTotal?.toNumber() ?? 0,
  }));
  const trendNote =
    previousRevenue === 0
      ? currentRevenue > 0
        ? 'Mulai ada penjualan'
        : 'Belum ada pembanding'
      : `${revenueTrend >= 0 ? '+' : ''}${formatPercent(revenueTrend)} vs periode lalu`;
  const stats = [
    {
      label: 'Omzet 30 hari',
      value: formatRupiah(currentRevenue),
      note: trendNote,
      icon: Banknote,
    },
    {
      label: 'Pesanan 30 hari',
      value: currentOrders.length.toLocaleString('id-ID'),
      note: `${ordersToday} masuk hari ini`,
      icon: ShoppingCart,
    },
    {
      label: 'Rata-rata pesanan',
      value: formatRupiah(averageOrderValue),
      note: `${paidOrders.length} pesanan dibayar`,
      icon: ReceiptText,
    },
    {
      label: 'Pengunjung 30 hari',
      value: uniqueVisitors.length.toLocaleString('id-ID'),
      note: `${pageViews.toLocaleString('id-ID')} kunjungan halaman`,
      icon: Users,
    },
    {
      label: 'Konversi',
      value: formatPercent(conversionRate),
      note: 'Pesanan ÷ pengunjung',
      icon: CircleCheck,
    },
    {
      label: 'Pembayaran berhasil',
      value: formatPercent(paidRate),
      note: `${formatPercent(cancellationRate)} dibatalkan`,
      icon: Banknote,
    },
  ];

  return (
    <>
      <div className="flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div>
          <p className="eyebrow">Dashboard owner</p>
          <h1 className="mt-2 font-display text-3xl font-medium tracking-tight sm:text-4xl">
            Kondisi bisnis Novastra.
          </h1>
          <p className="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground">
            Angka inti 30 hari terakhir untuk melihat penjualan, pelanggan, dan
            hal yang perlu ditindaklanjuti.
          </p>
        </div>
        <p className="text-xs text-muted-foreground">
          Diperbarui{' '}
          {new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'medium',
            timeStyle: 'short',
            timeZone: 'Asia/Makassar',
          }).format(now)}{' '}
          WITA
        </p>
      </div>

      <section
        className={cn(
          'mt-7 flex flex-col justify-between gap-4 rounded-xl border p-5 sm:flex-row sm:items-center',
          health.tone === 'healthy' && 'border-emerald-200 bg-emerald-50',
          health.tone === 'warning' && 'border-amber-200 bg-amber-50',
          health.tone === 'stable' && 'border-sky-200 bg-sky-50',
          health.tone === 'neutral' && 'border-ink/10 bg-white',
        )}
      >
        <div>
          <p className="text-xs font-semibold uppercase tracking-[0.15em] text-muted-foreground">
            Status bisnis
          </p>
          <h2 className="mt-2 font-display text-2xl font-semibold">
            {health.label}
          </h2>
          <p className="mt-1 text-sm text-muted-foreground">{health.summary}</p>
        </div>
        <div className="flex flex-wrap gap-x-5 gap-y-2 text-xs font-medium">
          <span>{formatPercent(paidRate)} dibayar</span>
          <span>{formatPercent(cancellationRate)} batal</span>
          <span>{lowStockProducts.length} stok kritis</span>
        </div>
      </section>

      <div className="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        {stats.map(({ label, value, note, icon: Icon }) => (
          <section
            key={label}
            className="rounded-xl border border-ink/10 bg-white p-5"
          >
            <div className="flex items-center justify-between gap-4">
              <p className="text-xs font-medium text-muted-foreground">
                {label}
              </p>
              <Icon className="size-4 shrink-0 text-brand" />
            </div>
            <p className="mt-4 break-words font-display text-2xl font-semibold sm:text-3xl">
              {value}
            </p>
            <p className="mt-1 text-xs text-muted-foreground">{note}</p>
          </section>
        ))}
      </div>

      <DashboardCharts series={[...daily.values()]} topProducts={topProducts} />

      <div className="mt-4 grid gap-4 xl:grid-cols-[1fr_320px]">
        <section className="overflow-hidden rounded-xl border border-ink/10 bg-white">
          <div className="flex items-center justify-between border-b border-ink/10 px-5 py-4">
            <div>
              <h2 className="font-display text-lg font-semibold">
                Pesanan terbaru
              </h2>
              <p className="mt-0.5 text-xs text-muted-foreground">
                Lima pesanan yang paling baru masuk.
              </p>
            </div>
            <Link
              href="/admin/orders"
              className="inline-flex items-center gap-1 text-xs font-medium text-brand hover:underline"
            >
              Semua <ArrowRight className="size-3.5" />
            </Link>
          </div>
          {recentOrders.length === 0 ? (
            <div className="px-5 py-14 text-center text-sm text-muted-foreground">
              Belum ada pesanan.
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full min-w-[680px] text-left text-sm">
                <thead className="bg-sand/50 text-xs text-muted-foreground">
                  <tr>
                    <th className="px-5 py-3 font-medium">Nomor</th>
                    <th className="px-5 py-3 font-medium">Pelanggan</th>
                    <th className="px-5 py-3 font-medium">Status</th>
                    <th className="px-5 py-3 font-medium">Pembayaran</th>
                    <th className="px-5 py-3 text-right font-medium">Total</th>
                  </tr>
                </thead>
                <tbody>
                  {recentOrders.map((order) => (
                    <tr key={order.id} className="border-t border-ink/10">
                      <td className="px-5 py-4 font-medium">
                        <Link
                          href={`/admin/orders/${order.id}`}
                          className="hover:underline"
                        >
                          {order.orderNumber}
                        </Link>
                      </td>
                      <td className="px-5 py-4">
                        {order.customerNameSnapshot}
                      </td>
                      <td className="px-5 py-4">
                        <Badge variant="outline">{order.status}</Badge>
                      </td>
                      <td className="px-5 py-4">{order.paymentStatus}</td>
                      <td className="px-5 py-4 text-right font-medium">
                        {formatRupiah(order.total.toNumber())}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </section>

        <section className="rounded-xl border border-ink/10 bg-white p-5">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="font-display text-lg font-semibold">
                Stok kritis
              </h2>
              <p className="mt-1 text-xs text-muted-foreground">
                Produk aktif dengan stok 5 atau kurang.
              </p>
            </div>
            <AlertTriangle className="size-4 text-amber-600" />
          </div>
          {lowStockProducts.length === 0 ? (
            <div className="py-10 text-center">
              <PackageSearch className="mx-auto size-5 text-brand" />
              <p className="mt-3 text-sm font-medium">Semua stok aman.</p>
            </div>
          ) : (
            <div className="mt-5 divide-y divide-ink/10">
              {lowStockProducts.map((product) => (
                <Link
                  key={product.id}
                  href={`/admin/products/${product.id}`}
                  className="flex items-center justify-between gap-4 py-3 text-sm hover:text-brand"
                >
                  <span className="min-w-0 truncate">{product.name}</span>
                  <Badge
                    variant={product.stock === 0 ? 'destructive' : 'outline'}
                  >
                    {product.stock}
                  </Badge>
                </Link>
              ))}
            </div>
          )}
          <p className="mt-5 border-t border-ink/10 pt-4 text-xs text-muted-foreground">
            {activeProducts} dari {totalProducts} produk sedang aktif.
          </p>
        </section>
      </div>
    </>
  );
}
