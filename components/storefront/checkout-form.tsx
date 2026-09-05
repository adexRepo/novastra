'use client';

import { useActionState, useEffect } from 'react';
import Link from 'next/link';
import { ArrowLeft, Check, LoaderCircle, LockKeyhole } from 'lucide-react';
import { submitCheckout } from '@/app/checkout/actions';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { formatRupiah } from '@/lib/catalog';
import { useCart } from './cart-provider';

type CheckoutFormProps = {
  customerName: string;
  customerEmail: string;
};

export function CheckoutForm({
  customerName,
  customerEmail,
}: CheckoutFormProps) {
  const { items, subtotal, clear } = useCart();
  const [state, action, pending] = useActionState(submitCheckout, undefined);
  const shipping = subtotal >= 250000 ? 0 : 25000;

  useEffect(() => {
    if (state?.orderId) clear();
  }, [state?.orderId, clear]);

  if (state?.orderId)
    return (
      <div className="grid min-h-[60vh] place-items-center text-center">
        <div>
          <span className="mx-auto grid size-16 place-items-center rounded-full bg-emerald-100 text-emerald-700">
            <Check className="size-7" />
          </span>
          <p className="eyebrow mt-6">Pesanan diterima</p>
          <h1 className="mt-3 font-display text-4xl font-medium tracking-tight">
            Terima kasih.
          </h1>
          <p className="mx-auto mt-3 max-w-md leading-7 text-muted-foreground">
            Pesanan <strong className="text-ink">{state.orderNumber}</strong>{' '}
            sudah tercatat. Kami akan mengirim detail berikutnya melalui email.
          </p>
          <div className="mt-7 flex justify-center gap-3">
            <Button
              render={<Link href={`/orders/${state.orderId}`} />}
              className="rounded-full"
            >
              Lihat pesanan
            </Button>
            <Button
              variant="outline"
              render={<Link href="/products" />}
              className="rounded-full"
            >
              Belanja lagi
            </Button>
          </div>
        </div>
      </div>
    );

  if (items.length === 0)
    return (
      <div className="grid min-h-[60vh] place-items-center text-center">
        <div>
          <h1 className="font-display text-3xl font-medium">
            Belum ada barang untuk dipesan.
          </h1>
          <Button
            render={<Link href="/products" />}
            className="mt-6 rounded-full"
          >
            Pilih produk
          </Button>
        </div>
      </div>
    );

  return (
    <div className="page-shell py-10 sm:py-14">
      <Link
        href="/cart"
        className="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-ink"
      >
        <ArrowLeft className="size-4" />
        Kembali ke keranjang
      </Link>
      <div className="mt-7 grid gap-10 lg:grid-cols-[1fr_390px] lg:gap-16">
        <form action={action}>
          <input
            type="hidden"
            name="cart"
            value={JSON.stringify(
              items.map(({ product, quantity }) => ({
                productId: product.id,
                quantity,
              })),
            )}
          />
          <p className="eyebrow">Checkout</p>
          <h1 className="section-title mt-3">Detail pengiriman.</h1>
          <div className="mt-9 grid gap-5 sm:grid-cols-2">
            <div className="sm:col-span-2">
              <Label htmlFor="name">Nama lengkap</Label>
              <Input
                id="name"
                name="name"
                required
                minLength={2}
                maxLength={100}
                autoComplete="name"
                defaultValue={customerName}
                className="mt-2 h-10 bg-white"
                placeholder="Nama penerima"
              />
            </div>
            <div>
              <Label htmlFor="email">Email pelanggan</Label>
              <Input
                id="email"
                name="email"
                required
                type="email"
                maxLength={160}
                autoComplete="email"
                value={customerEmail}
                readOnly
                className="mt-2 h-10 bg-white"
                placeholder="nama@email.com"
              />
            </div>
            <div>
              <Label htmlFor="phone">Nomor WhatsApp</Label>
              <Input
                id="phone"
                name="phone"
                required
                inputMode="tel"
                maxLength={20}
                autoComplete="tel"
                className="mt-2 h-10 bg-white"
                placeholder="08xxxxxxxxxx"
              />
            </div>
            <div className="sm:col-span-2">
              <Label htmlFor="address">Alamat lengkap</Label>
              <Textarea
                id="address"
                name="address"
                required
                minLength={10}
                maxLength={500}
                className="mt-2 min-h-28 bg-white"
                placeholder="Jalan, nomor rumah, kelurahan, kecamatan, kota, kode pos"
              />
            </div>
            <div className="sm:col-span-2">
              <Label htmlFor="notes">
                Catatan{' '}
                <span className="font-normal text-muted-foreground">
                  (opsional)
                </span>
              </Label>
              <Textarea
                id="notes"
                name="notes"
                maxLength={300}
                className="mt-2 min-h-20 bg-white"
                placeholder="Patokan alamat atau catatan untuk pesanan"
              />
            </div>
          </div>
          <div className="mt-8 rounded-xl border border-ink/10 bg-white p-4 text-sm">
            <div className="flex gap-3">
              <LockKeyhole className="mt-0.5 size-4 shrink-0 text-brand" />
              <div>
                <p className="font-medium">Login pelanggan diperlukan</p>
                <p className="mt-1 leading-6 text-muted-foreground">
                  Identitas pelanggan dibaca dari sesi Auth.js di server. Harga,
                  stok, dan total selalu dihitung ulang dari database.
                </p>
              </div>
            </div>
          </div>
          {state?.error && (
            <p
              role="alert"
              className="mt-5 rounded-lg bg-red-50 p-3 text-sm text-red-700"
            >
              {state.error}
            </p>
          )}
          <Button
            type="submit"
            size="lg"
            className="mt-6 h-11 w-full rounded-full sm:w-auto sm:px-8"
            disabled={pending}
          >
            {pending ? (
              <>
                <LoaderCircle className="animate-spin" />
                Membuat pesanan...
              </>
            ) : (
              'Buat pesanan'
            )}
          </Button>
        </form>
        <aside className="h-fit rounded-2xl bg-white p-6 lg:sticky lg:top-28">
          <h2 className="font-display text-2xl font-medium">Pesanan Anda</h2>
          <div className="mt-5 space-y-4 border-b border-ink/10 pb-5">
            {items.map(({ product, quantity }) => (
              <div
                key={product.id}
                className="flex justify-between gap-4 text-sm"
              >
                <span>
                  {quantity} × {product.name}
                </span>
                <span className="shrink-0 font-medium">
                  {formatRupiah(product.price * quantity)}
                </span>
              </div>
            ))}
          </div>
          <dl className="mt-5 space-y-3 text-sm">
            <div className="flex justify-between">
              <dt className="text-muted-foreground">Subtotal</dt>
              <dd>{formatRupiah(subtotal)}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="text-muted-foreground">Pengiriman</dt>
              <dd>{shipping === 0 ? 'Gratis' : formatRupiah(shipping)}</dd>
            </div>
            <div className="flex justify-between border-t border-ink/10 pt-4 text-base font-semibold">
              <dt>Total</dt>
              <dd>{formatRupiah(subtotal + shipping)}</dd>
            </div>
          </dl>
        </aside>
      </div>
    </div>
  );
}
