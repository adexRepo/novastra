import { AdminPageHeader } from '@/components/admin/page-header';
import { Badge } from '@/components/ui/badge';
import { prisma } from '@/lib/db/prisma';
import { formatRupiah } from '@/lib/catalog';

export default async function PaymentsPage() {
  const orders = await prisma.order.findMany({
    where: { paymentStatus: { not: 'UNPAID' } },
    orderBy: { updatedAt: 'desc' },
    take: 100,
  });
  return (
    <>
      <AdminPageHeader
        eyebrow="Keuangan"
        title="Pembayaran"
        description="Status pembayaran hanya dapat diubah oleh admin."
      />
      <div className="mt-7 overflow-hidden rounded-xl border border-ink/10 bg-white">
        <table className="w-full min-w-[640px] text-left text-sm">
          <thead className="bg-sand/50 text-xs text-muted-foreground">
            <tr>
              <th className="px-5 py-3 font-medium">Pesanan</th>
              <th className="px-5 py-3 font-medium">Pelanggan</th>
              <th className="px-5 py-3 font-medium">Status</th>
              <th className="px-5 py-3 text-right font-medium">Total</th>
            </tr>
          </thead>
          <tbody>
            {orders.map((order) => (
              <tr key={order.id} className="border-t border-ink/10">
                <td className="px-5 py-4 font-medium">{order.orderNumber}</td>
                <td className="px-5 py-4">{order.customerNameSnapshot}</td>
                <td className="px-5 py-4">
                  <Badge variant="outline">{order.paymentStatus}</Badge>
                </td>
                <td className="px-5 py-4 text-right font-medium">
                  {formatRupiah(order.total.toNumber())}
                </td>
              </tr>
            ))}
            {orders.length === 0 && (
              <tr>
                <td
                  colSpan={4}
                  className="px-5 py-16 text-center text-muted-foreground"
                >
                  Belum ada catatan pembayaran.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </>
  );
}
