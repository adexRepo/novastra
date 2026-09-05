'use client';

import {
  Bar,
  BarChart,
  CartesianGrid,
  Line,
  LineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import { formatRupiah } from '@/lib/catalog';

export type DashboardPoint = {
  day: string;
  label: string;
  orders: number;
  revenue: number;
  visits: number;
};

export type TopProductPoint = {
  name: string;
  quantity: number;
  revenue: number;
};

const compactNumber = new Intl.NumberFormat('id-ID', {
  notation: 'compact',
  maximumFractionDigits: 1,
});

function ChartEmpty({ children }: { children: React.ReactNode }) {
  return (
    <div className="grid h-[240px] place-items-center text-center text-sm text-muted-foreground">
      {children}
    </div>
  );
}

export function DashboardCharts({
  series,
  topProducts,
}: {
  series: DashboardPoint[];
  topProducts: TopProductPoint[];
}) {
  const hasSales = series.some((point) => point.revenue > 0);
  const hasVisits = series.some((point) => point.visits > 0);
  const topQuantity = Math.max(
    ...topProducts.map((product) => product.quantity),
    1,
  );

  return (
    <>
      <div className="mt-6 grid gap-4 xl:grid-cols-2">
        <section className="min-w-0 rounded-xl border border-ink/10 bg-white p-4 sm:p-5">
          <div>
            <h2 className="font-display text-lg font-semibold">Omzet harian</h2>
            <p className="mt-1 text-xs text-muted-foreground">
              Pesanan berstatus sudah dibayar, 30 hari terakhir.
            </p>
          </div>
          {hasSales ? (
            <div
              className="mt-5 h-[240px] w-full"
              aria-label="Grafik omzet harian 30 hari terakhir"
            >
              <ResponsiveContainer width="100%" height="100%">
                <BarChart
                  data={series}
                  margin={{ left: -14, right: 4, top: 4 }}
                >
                  <CartesianGrid vertical={false} stroke="#e3e9df" />
                  <XAxis
                    dataKey="label"
                    axisLine={false}
                    tickLine={false}
                    interval={5}
                    tick={{ fill: '#647267', fontSize: 11 }}
                  />
                  <YAxis
                    axisLine={false}
                    tickLine={false}
                    tickFormatter={(value) =>
                      compactNumber.format(Number(value))
                    }
                    tick={{ fill: '#647267', fontSize: 11 }}
                  />
                  <Tooltip
                    cursor={{ fill: '#e4efdf', opacity: 0.45 }}
                    formatter={(value) => [
                      formatRupiah(Number(value)),
                      'Omzet',
                    ]}
                    labelFormatter={(label) => `Tanggal ${label}`}
                  />
                  <Bar
                    dataKey="revenue"
                    fill="#238457"
                    radius={[4, 4, 0, 0]}
                    maxBarSize={22}
                  />
                </BarChart>
              </ResponsiveContainer>
            </div>
          ) : (
            <ChartEmpty>
              Grafik akan muncul setelah ada pesanan yang dibayar.
            </ChartEmpty>
          )}
        </section>

        <section className="min-w-0 rounded-xl border border-ink/10 bg-white p-4 sm:p-5">
          <div>
            <h2 className="font-display text-lg font-semibold">
              Kunjungan toko
            </h2>
            <p className="mt-1 text-xs text-muted-foreground">
              Tampilan halaman publik anonim, 30 hari terakhir.
            </p>
          </div>
          {hasVisits ? (
            <div
              className="mt-5 h-[240px] w-full"
              aria-label="Grafik kunjungan toko 30 hari terakhir"
            >
              <ResponsiveContainer width="100%" height="100%">
                <LineChart
                  data={series}
                  margin={{ left: -22, right: 8, top: 4 }}
                >
                  <CartesianGrid vertical={false} stroke="#e3e9df" />
                  <XAxis
                    dataKey="label"
                    axisLine={false}
                    tickLine={false}
                    interval={5}
                    tick={{ fill: '#647267', fontSize: 11 }}
                  />
                  <YAxis
                    allowDecimals={false}
                    axisLine={false}
                    tickLine={false}
                    tick={{ fill: '#647267', fontSize: 11 }}
                  />
                  <Tooltip
                    formatter={(value) => [Number(value), 'Kunjungan']}
                    labelFormatter={(label) => `Tanggal ${label}`}
                  />
                  <Line
                    type="monotone"
                    dataKey="visits"
                    stroke="#238457"
                    strokeWidth={2}
                    dot={false}
                    activeDot={{ r: 4 }}
                  />
                </LineChart>
              </ResponsiveContainer>
            </div>
          ) : (
            <ChartEmpty>
              Kunjungan akan tercatat setelah migration database diterapkan.
            </ChartEmpty>
          )}
        </section>
      </div>

      <section className="mt-4 rounded-xl border border-ink/10 bg-white p-4 sm:p-5">
        <h2 className="font-display text-lg font-semibold">
          Produk paling sering dipesan
        </h2>
        <p className="mt-1 text-xs text-muted-foreground">
          Berdasarkan jumlah item pada pesanan yang sudah dibayar.
        </p>
        {topProducts.length === 0 ? (
          <div className="py-14 text-center text-sm text-muted-foreground">
            Data produk terlaris belum tersedia.
          </div>
        ) : (
          <div className="mt-6 space-y-5">
            {topProducts.map((product, index) => (
              <div key={product.name}>
                <div className="mb-2 flex items-start justify-between gap-4 text-sm">
                  <div className="min-w-0">
                    <span className="mr-2 text-xs font-semibold text-muted-foreground">
                      {index + 1}
                    </span>
                    <span className="font-medium">{product.name}</span>
                  </div>
                  <div className="shrink-0 text-right">
                    <p className="font-semibold">{product.quantity} item</p>
                    <p className="text-[11px] text-muted-foreground">
                      {formatRupiah(product.revenue)}
                    </p>
                  </div>
                </div>
                <div className="h-2 overflow-hidden rounded-full bg-sand">
                  <div
                    className="h-full rounded-full bg-brand"
                    style={{
                      width: `${Math.max((product.quantity / topQuantity) * 100, 5)}%`,
                    }}
                  />
                </div>
              </div>
            ))}
          </div>
        )}
      </section>
    </>
  );
}
