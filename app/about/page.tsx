import type { Metadata } from 'next';
import { SiteFooter } from '@/components/storefront/site-footer';
import { SiteHeader } from '@/components/storefront/site-header';

export const metadata: Metadata = { title: 'Tentang Kami' };
export default function AboutPage() {
  return (
    <main>
      <SiteHeader />
      <section className="page-shell py-14 sm:py-20">
        <p className="eyebrow">Tentang Novastra</p>
        <h1 className="mt-4 max-w-[13ch] font-display text-5xl font-medium leading-[0.96] tracking-[-0.055em] sm:text-7xl">
          Segar, bersih, dan dekat dengan dapur Anda.
        </h1>
        <div className="mt-14 grid gap-10 border-t border-ink/15 pt-10 md:grid-cols-2 md:gap-20">
          <p className="font-display text-2xl font-medium leading-tight">
            Bahan yang baik membuat masak di rumah terasa jauh lebih sederhana.
          </p>
          <div className="space-y-5 leading-7 text-muted-foreground">
            <p>
              Novastra memilih ayam, ikan, sayur, dan bumbu yang ditangani
              dengan bersih. Produk segar disimpan dingin dan dikemas seperlunya
              agar kualitasnya tetap terjaga sampai ke dapur Anda.
            </p>
            <p>
              Kami bekerja sebagai bisnis Indonesia yang sedang tumbuh. Karena
              itu, layanan kami dibuat manusiawi: informasi stok yang jujur,
              harga yang jelas, dan bantuan yang mudah dihubungi.
            </p>
          </div>
        </div>
        <div
          className="mt-16 aspect-[16/7] rounded-2xl bg-[url('/uploads/products/novastra-fresh-collection.webp')] bg-cover bg-center"
          aria-hidden="true"
        />
      </section>
      <SiteFooter />
    </main>
  );
}
