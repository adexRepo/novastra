'use client';

import Link from 'next/link';
import { ArrowRight, Minus, Plus, ShoppingBag, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { formatRupiah } from '@/lib/catalog';
import { useCart } from './cart-provider';
import { ProductImage } from './product-image';

export function CartPage() {
  const { items, subtotal, updateItem, removeItem } = useCart();
  const shipping = subtotal >= 250000 ? 0 : 25000;
  if (items.length === 0)
    return (
      <section className="page-shell grid min-h-[65vh] place-items-center py-20 text-center">
        <div>
          <span className="mx-auto grid size-16 place-items-center rounded-full bg-sand">
            <ShoppingBag className="size-6" />
          </span>
          <h1 className="mt-6 font-display text-4xl font-medium tracking-tight">
            Keranjang Anda masih kosong.
          </h1>
          <p className="mt-3 text-muted-foreground">
            Mari temukan sesuatu yang akan sering Anda pakai.
          </p>
          <Button
            render={<Link href="/products" />}
            className="mt-7 rounded-full"
          >
            Lihat produk <ArrowRight />
          </Button>
        </div>
      </section>
    );
  return (
    <section className="page-shell min-h-[70vh] py-12 sm:py-16">
      <p className="eyebrow">Keranjang</p>
      <h1 className="section-title mt-3">Pilihan Anda.</h1>
      <div className="mt-10 grid gap-10 lg:grid-cols-[1fr_380px] lg:gap-16">
        <div className="border-t border-ink/15">
          {items.map(({ product, quantity }) => (
            <article
              key={product.id}
              className="grid grid-cols-[96px_1fr] gap-4 border-b border-ink/15 py-5 sm:grid-cols-[128px_1fr_auto] sm:items-center sm:gap-6"
            >
              <div className="relative aspect-square overflow-hidden rounded-xl bg-sand">
                <ProductImage position={product.position} name={product.name} />
              </div>
              <div>
                <Link
                  href={`/products/${product.slug}`}
                  className="font-display text-xl font-medium hover:underline"
                >
                  {product.name}
                </Link>
                <p className="mt-1 text-xs text-muted-foreground">
                  {product.category} · {product.sku}
                </p>
                <p className="mt-3 text-sm font-medium sm:hidden">
                  {formatRupiah(product.price * quantity)}
                </p>
                <div className="mt-4 flex w-fit items-center rounded-full border border-ink/15 bg-white p-0.5">
                  <Button
                    variant="ghost"
                    size="icon-sm"
                    className="rounded-full"
                    aria-label="Kurangi"
                    onClick={() => updateItem(product.id, quantity - 1)}
                  >
                    <Minus />
                  </Button>
                  <span className="w-8 text-center text-xs font-medium">
                    {quantity}
                  </span>
                  <Button
                    variant="ghost"
                    size="icon-sm"
                    className="rounded-full"
                    aria-label="Tambah"
                    onClick={() => updateItem(product.id, quantity + 1)}
                  >
                    <Plus />
                  </Button>
                </div>
              </div>
              <div className="col-start-2 flex items-center justify-between sm:col-start-auto sm:block sm:text-right">
                <p className="hidden text-sm font-medium sm:block">
                  {formatRupiah(product.price * quantity)}
                </p>
                <Button
                  variant="ghost"
                  size="sm"
                  className="text-muted-foreground sm:mt-5"
                  onClick={() => removeItem(product.id)}
                >
                  <Trash2 />
                  Hapus
                </Button>
              </div>
            </article>
          ))}
        </div>
        <aside className="h-fit rounded-2xl bg-white p-6 shadow-[0_1px_0_rgb(35_34_30/8%)] sm:p-7">
          <h2 className="font-display text-2xl font-medium">Ringkasan</h2>
          <dl className="mt-6 space-y-4 text-sm">
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
          {subtotal < 250000 && (
            <p className="mt-5 rounded-lg bg-sand/60 p-3 text-xs leading-5 text-muted-foreground">
              Tambah {formatRupiah(250000 - subtotal)} lagi untuk gratis
              pengiriman.
            </p>
          )}
          <Button
            render={<Link href="/checkout" />}
            size="lg"
            className="mt-6 h-11 w-full rounded-full"
          >
            Lanjut ke checkout <ArrowRight />
          </Button>
          <p className="mt-4 text-center text-[11px] leading-5 text-muted-foreground">
            Harga dan ketersediaan akan diverifikasi kembali saat pesanan
            dibuat.
          </p>
        </aside>
      </div>
    </section>
  );
}
