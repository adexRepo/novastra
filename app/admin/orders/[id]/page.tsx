import Link from 'next/link';
import { notFound } from 'next/navigation';
import { ArrowLeft } from 'lucide-react';
import { updateOrderStatus, updatePaymentStatus } from './actions';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { prisma } from '@/lib/db/prisma';
import { formatRupiah } from '@/lib/catalog';

export default async function AdminOrderDetail({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const order = await prisma.order.findUnique({
    where: { id: (await params).id },
    include: { items: true },
  });
  if (!order) notFound();
  return (
    <>
      <Link
        href="/admin/orders"
        className="inline-flex items-center gap-2 text-sm text-muted-foreground"
      >
        <ArrowLeft className="size-4" />
        Semua pesanan
      </Link>
      <div className="mt-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
          <p className="eyebrow">Pesanan</p>
          <h1 className="mt-2 font-display text-3xl font-medium">
            {order.orderNumber}
          </h1>
          <p className="mt-2 text-sm text-muted-foreground">
            {order.customerNameSnapshot} · {order.customerEmailSnapshot}
          </p>
        </div>
        <div className="flex gap-2">
          <Badge variant="outline">{order.status}</Badge>
          <Badge variant="secondary">{order.paymentStatus}</Badge>
        </div>
      </div>
      <div className="mt-7 grid gap-5 lg:grid-cols-[1fr_340px]">
        <section className="rounded-xl border border-ink/10 bg-white p-6">
          <h2 className="font-display text-xl font-medium">Item pesanan</h2>
          <div className="mt-4 divide-y divide-ink/10">
            {order.items.map((item) => (
              <div
                key={item.id}
                className="flex justify-between gap-4 py-4 text-sm"
              >
                <div>
                  <p className="font-medium">{item.productName}</p>
                  <p className="mt-1 text-xs text-muted-foreground">
                    {item.quantity} × {formatRupiah(item.unitPrice.toNumber())}
                  </p>
                </div>
                <p>{formatRupiah(item.lineTotal.toNumber())}</p>
              </div>
            ))}
          </div>
          <div className="mt-5 flex justify-between border-t border-ink/10 pt-5 font-semibold">
            <span>Total</span>
            <span>{formatRupiah(order.total.toNumber())}</span>
          </div>
        </section>
        <aside className="space-y-4">
          <section className="rounded-xl border border-ink/10 bg-white p-5">
            <h2 className="font-medium">Ubah status pesanan</h2>
            <div className="mt-4 flex flex-wrap gap-2">
              {(
                [
                  'CONFIRMED',
                  'PROCESSING',
                  'SHIPPED',
                  'COMPLETED',
                  'CANCELLED',
                ] as const
              ).map((target) => (
                <form key={target} action={updateOrderStatus}>
                  <input type="hidden" name="id" value={order.id} />
                  <input type="hidden" name="target" value={target} />
                  <Button
                    type="submit"
                    variant="outline"
                    size="sm"
                    disabled={order.status === target}
                  >
                    {target}
                  </Button>
                </form>
              ))}
            </div>
          </section>
          <section className="rounded-xl border border-ink/10 bg-white p-5">
            <h2 className="font-medium">Ubah pembayaran</h2>
            <div className="mt-4 flex flex-wrap gap-2">
              {(['PENDING', 'PAID', 'FAILED', 'REFUNDED'] as const).map(
                (target) => (
                  <form key={target} action={updatePaymentStatus}>
                    <input type="hidden" name="id" value={order.id} />
                    <input type="hidden" name="target" value={target} />
                    <Button
                      type="submit"
                      variant="outline"
                      size="sm"
                      disabled={order.paymentStatus === target}
                    >
                      {target}
                    </Button>
                  </form>
                ),
              )}
            </div>
          </section>
        </aside>
      </div>
    </>
  );
}
