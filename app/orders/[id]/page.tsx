import Link from 'next/link';
import { notFound, redirect } from 'next/navigation';
import { ArrowLeft } from 'lucide-react';
import { auth } from '@/auth';
import { SiteFooter } from '@/components/storefront/site-footer';
import { SiteHeader } from '@/components/storefront/site-header';
import { Badge } from '@/components/ui/badge';
import { prisma } from '@/lib/db/prisma';
import { formatRupiah } from '@/lib/catalog';

export const dynamic = 'force-dynamic';
export default async function OrderDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const session = await auth();
  if (!session?.user?.email || session.user.role !== 'CUSTOMER')
    redirect(`/login?callbackUrl=${encodeURIComponent(`/orders/${id}`)}`);
  const order = await prisma.order.findUnique({
    where: { id },
    include: { items: true, customer: true },
  });
  if (!order) notFound();
  if (order.customer.email !== session.user.email) notFound();
  return (
    <main>
      <SiteHeader />
      <section className="page-shell min-h-[65vh] py-12">
        <Link
          href="/orders"
          className="inline-flex items-center gap-2 text-sm text-muted-foreground"
        >
          <ArrowLeft className="size-4" />
          Semua pesanan
        </Link>
        <div className="mt-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
          <div>
            <p className="eyebrow">Detail pesanan</p>
            <h1 className="mt-3 font-display text-3xl font-medium">
              {order.orderNumber}
            </h1>
            <p className="mt-2 text-sm text-muted-foreground">
              {new Intl.DateTimeFormat('id-ID', {
                dateStyle: 'long',
                timeStyle: 'short',
              }).format(order.createdAt)}
            </p>
          </div>
          <div className="flex gap-2">
            <Badge variant="outline">{order.status}</Badge>
            <Badge variant="secondary">{order.paymentStatus}</Badge>
          </div>
        </div>
        <div className="mt-8 grid gap-6 lg:grid-cols-[1fr_360px]">
          <div className="rounded-xl border border-ink/10 bg-white p-5">
            <h2 className="font-display text-xl font-medium">Produk</h2>
            <div className="mt-5 divide-y divide-ink/10">
              {order.items.map((item) => (
                <div
                  key={item.id}
                  className="flex justify-between gap-4 py-4 text-sm"
                >
                  <div>
                    <p className="font-medium">{item.productName}</p>
                    <p className="mt-1 text-xs text-muted-foreground">
                      {item.quantity} ×{' '}
                      {formatRupiah(item.unitPrice.toNumber())}
                    </p>
                  </div>
                  <p className="font-medium">
                    {formatRupiah(item.lineTotal.toNumber())}
                  </p>
                </div>
              ))}
            </div>
          </div>
          <aside className="h-fit rounded-xl bg-ink p-6 text-paper">
            <h2 className="font-display text-xl font-medium">Ringkasan</h2>
            <dl className="mt-5 space-y-3 text-sm">
              <div className="flex justify-between text-paper/60">
                <dt>Subtotal</dt>
                <dd>{formatRupiah(order.subtotal.toNumber())}</dd>
              </div>
              <div className="flex justify-between text-paper/60">
                <dt>Pengiriman</dt>
                <dd>{formatRupiah(order.shippingTotal.toNumber())}</dd>
              </div>
              <div className="flex justify-between border-t border-white/15 pt-4 text-base font-semibold">
                <dt>Total</dt>
                <dd>{formatRupiah(order.total.toNumber())}</dd>
              </div>
            </dl>
            <p className="mt-6 text-xs leading-5 text-paper/50">
              Dikirim ke {order.addressSnapshot}. Nomor kontak:{' '}
              {order.phoneSnapshot}.
            </p>
          </aside>
        </div>
      </section>
      <SiteFooter />
    </main>
  );
}
