import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import { ArrowLeft, PackageCheck, ShieldCheck, Snowflake } from 'lucide-react';
import { AddToCart } from '@/components/storefront/add-to-cart';
import { ProductImage } from '@/components/storefront/product-image';
import { SiteFooter } from '@/components/storefront/site-footer';
import { SiteHeader } from '@/components/storefront/site-header';
import { Badge } from '@/components/ui/badge';
import { formatRupiah, getProduct, products } from '@/lib/catalog';

type Props = { params: Promise<{ slug: string }> };
export function generateStaticParams() {
  return products.map(({ slug }) => ({ slug }));
}
export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const product = getProduct((await params).slug);
  return product
    ? { title: product.name, description: product.shortDescription }
    : {};
}

export default async function ProductDetailPage({ params }: Props) {
  const product = getProduct((await params).slug);
  if (!product) notFound();
  return (
    <main>
      <SiteHeader />
      <div className="page-shell py-8 sm:py-12">
        <Link
          href="/products"
          className="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-ink"
        >
          <ArrowLeft className="size-4" />
          Kembali ke produk
        </Link>
        <div className="mt-7 grid gap-10 lg:grid-cols-2 lg:gap-16">
          <div className="relative aspect-square overflow-hidden rounded-2xl bg-sand">
            <ProductImage position={product.position} name={product.name} />
          </div>
          <div className="flex flex-col justify-center">
            <div className="flex items-center gap-3">
              <Badge variant="outline">{product.category}</Badge>
              <span className="text-xs text-muted-foreground">
                SKU {product.sku}
              </span>
            </div>
            <h1 className="mt-5 font-display text-4xl font-medium leading-none tracking-[-0.045em] sm:text-6xl">
              {product.name}
            </h1>
            <p className="mt-5 text-2xl font-medium">
              {formatRupiah(product.price)}
            </p>
            <p className="mt-6 max-w-xl leading-7 text-muted-foreground">
              {product.description}
            </p>
            <div className="mt-6 flex items-center gap-2 text-sm">
              <span
                className={`size-2 rounded-full ${product.stock > 0 ? 'bg-emerald-600' : 'bg-stone-400'}`}
              />
              <span>
                {product.stock > 5
                  ? 'Tersedia, siap dikirim'
                  : product.stock > 0
                    ? `Hanya ${product.stock} tersisa`
                    : 'Stok habis'}
              </span>
            </div>
            <AddToCart product={product} />
            <div className="mt-8 grid gap-3 border-t border-ink/10 pt-6 text-sm text-muted-foreground sm:grid-cols-3">
              <span className="flex items-center gap-2">
                <PackageCheck className="size-4" />
                Dikemas bersih
              </span>
              <span className="flex items-center gap-2">
                <Snowflake className="size-4" />
                Rantai dingin
              </span>
              <span className="flex items-center gap-2">
                <ShieldCheck className="size-4" />
                Pembayaran aman
              </span>
            </div>
          </div>
        </div>
      </div>
      <SiteFooter />
    </main>
  );
}
