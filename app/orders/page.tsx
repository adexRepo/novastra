import type { Metadata } from 'next';
import Link from 'next/link';
import { redirect } from 'next/navigation';
import { auth } from '@/auth';
import { SiteFooter } from '@/components/storefront/site-footer';
import { SiteHeader } from '@/components/storefront/site-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { prisma } from '@/lib/db/prisma';
import { formatRupiah } from '@/lib/catalog';

export const metadata: Metadata = { title: 'Pesanan Saya' };
export const dynamic = 'force-dynamic';
export default async function OrdersPage() {
  const session = await auth();
  if (!session?.user?.email || session.user.role !== 'CUSTOMER') {
    redirect('/login?callbackUrl=%2Forders');
  }
  const customer = await prisma.customer.findUnique({
    where: { email: session.user.email },
  });
  const orders = customer
    ? await prisma.order.findMany({
        where: { customerId: customer.id },
        orderBy: { createdAt: 'desc' },
        include: { items: true },
      })
    : [];
  return (
    <main>
      <SiteHeader />
      <section className="page-shell min-h-[65vh] py-12 sm:py-16">
        <p className="eyebrow">Akun saya</p>
        <h1 className="section-title mt-3">Pesanan.</h1>
        {orders.length === 0 ? (
          <div className="my-24 text-center">
            <p className="font-display text-2xl font-medium">
              Belum ada pesanan.
            </p>
            <p className="mt-2 text-sm text-muted-foreground">
              Pesanan pertama Anda akan muncul di sini.
            </p>
            <Button
              render={<Link href="/products" />}
              className="mt-6 rounded-full"
            >
              Mulai belanja
            </Button>
          </div>
        ) : (
          <div className="mt-10 space-y-4">
            {orders.map((order) => (
              <Link
                key={order.id}
                href={`/orders/${order.id}`}
                className="grid gap-4 rounded-xl border border-ink/10 bg-white p-5 transition-colors hover:border-ink/30 sm:grid-cols-[1fr_auto_auto] sm:items-center"
              >
                <div>
                  <p className="font-medium">{order.orderNumber}</p>
                  <p className="mt-1 text-xs text-muted-foreground">
                    {new Intl.DateTimeFormat('id-ID', {
                      dateStyle: 'long',
                    }).format(order.createdAt)}{' '}
                    · {order.items.length} jenis produk
                  </p>
                </div>
                <div className="flex gap-2">
                  <Badge variant="outline">{order.status}</Badge>
                  <Badge variant="secondary">{order.paymentStatus}</Badge>
                </div>
                <p className="font-medium">
                  {formatRupiah(order.total.toNumber())}
                </p>
              </Link>
            ))}
          </div>
        )}
      </section>
      <SiteFooter />
    </main>
  );
}
