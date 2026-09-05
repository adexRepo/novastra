import Link from 'next/link';
import { AdminPageHeader } from '@/components/admin/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { prisma } from '@/lib/db/prisma';
import { formatRupiah } from '@/lib/catalog';

export default async function OrdersPage() {
  const orders = await prisma.order.findMany({
    orderBy: { createdAt: 'desc' },
    take: 100,
  });
  return (
    <>
      <AdminPageHeader
        eyebrow="Operasional"
        title="Pesanan"
        description="Konfirmasi, proses, dan pantau pesanan pelanggan."
      />
      <div className="mt-7 overflow-hidden rounded-xl border border-ink/10 bg-white">
        <div className="overflow-x-auto">
          <table className="w-full min-w-[780px] text-left text-sm">
            <thead className="bg-sand/50 text-xs text-muted-foreground">
              <tr>
                <th className="px-5 py-3 font-medium">Nomor</th>
                <th className="px-5 py-3 font-medium">Pelanggan</th>
                <th className="px-5 py-3 font-medium">Tanggal</th>
                <th className="px-5 py-3 font-medium">Status</th>
                <th className="px-5 py-3 text-right font-medium">Total</th>
                <th className="px-5 py-3">
                  <span className="sr-only">Aksi</span>
                </th>
              </tr>
            </thead>
            <tbody>
              {orders.map((order) => (
                <tr key={order.id} className="border-t border-ink/10">
                  <td className="px-5 py-4 font-medium">{order.orderNumber}</td>
                  <td className="px-5 py-4">
                    <p>{order.customerNameSnapshot}</p>
                    <p className="text-xs text-muted-foreground">
                      {order.customerEmailSnapshot}
                    </p>
                  </td>
                  <td className="px-5 py-4 text-muted-foreground">
                    {new Intl.DateTimeFormat('id-ID', {
                      dateStyle: 'medium',
                    }).format(order.createdAt)}
                  </td>
                  <td className="px-5 py-4">
                    <Badge variant="outline">{order.status}</Badge>
                  </td>
                  <td className="px-5 py-4 text-right font-medium">
                    {formatRupiah(order.total.toNumber())}
                  </td>
                  <td className="px-5 py-4 text-right">
                    <Button
                      variant="ghost"
                      size="sm"
                      render={<Link href={`/admin/orders/${order.id}`} />}
                    >
                      Buka
                    </Button>
                  </td>
                </tr>
              ))}
              {orders.length === 0 && (
                <tr>
                  <td
                    colSpan={6}
                    className="px-5 py-16 text-center text-muted-foreground"
                  >
                    Belum ada pesanan.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </>
  );
}
