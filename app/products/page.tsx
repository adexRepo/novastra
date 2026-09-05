import type { Metadata } from 'next';
import { ProductsBrowser } from '@/components/storefront/products-browser';
import { SiteFooter } from '@/components/storefront/site-footer';
import { SiteHeader } from '@/components/storefront/site-header';

export const metadata: Metadata = {
  title: 'Produk',
  description:
    'Temukan ayam, ikan, sayur, dan bumbu segar untuk kebutuhan dapur Anda.',
};

export default function ProductsPage() {
  return (
    <main>
      <SiteHeader />
      <section className="page-shell min-h-[70vh] py-12 sm:py-16">
        <p className="eyebrow">Katalog</p>
        <div className="mt-3 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
          <h1 className="section-title">
            Barang baik,
            <br />
            dipilih dengan tenang.
          </h1>
          <p className="max-w-sm text-sm leading-6 text-muted-foreground">
            Koleksi yang ringkas agar Anda tak perlu menyaring terlalu banyak
            hal untuk menemukan yang tepat.
          </p>
        </div>
        <ProductsBrowser />
      </section>
      <SiteFooter />
    </main>
  );
}
