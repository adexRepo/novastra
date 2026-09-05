import Link from 'next/link';
import { ArrowRight, ChevronRight, ShoppingBag, Star } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { SiteHeader } from '@/components/storefront/site-header';

const products = [
  {
    name: 'Dada Ayam Fillet 500 g',
    slug: 'dada-ayam-fillet-500g',
    category: 'Ayam & Daging',
    price: 'Rp42.000',
    position: '0% 0%',
    badge: 'Pilihan kami',
  },
  {
    name: 'Fillet Ikan Dori 500 g',
    slug: 'fillet-ikan-dori-500g',
    category: 'Ikan & Seafood',
    price: 'Rp48.000',
    position: '100% 0%',
    badge: 'Baru',
  },
  {
    name: 'Pakcoy Segar 250 g',
    slug: 'pakcoy-segar-250g',
    category: 'Sayur & Buah',
    price: 'Rp12.000',
    position: '0% 100%',
    badge: 'Terlaris',
  },
  {
    name: 'Paket Bumbu Dasar Merah',
    slug: 'paket-bumbu-dasar-merah',
    category: 'Bumbu & Rempah',
    price: 'Rp24.000',
    position: '100% 100%',
    badge: 'Stok terbatas',
  },
];

const categories = [
  {
    number: '01',
    name: 'Ayam & daging',
    slug: 'ayam-daging',
    count: '2 produk',
  },
  {
    number: '02',
    name: 'Ikan & seafood',
    slug: 'ikan-seafood',
    count: '2 produk',
  },
  { number: '03', name: 'Sayur & buah', slug: 'sayur-buah', count: '2 produk' },
  {
    number: '04',
    name: 'Bumbu & rempah',
    slug: 'bumbu-rempah',
    count: '2 produk',
  },
];

export default function Home() {
  return (
    <main>
      <SiteHeader />

      <section className="page-shell grid min-h-[630px] items-center gap-10 py-12 md:grid-cols-[0.82fr_1.18fr] md:py-16 lg:min-h-[720px] lg:gap-20">
        <div className="max-w-xl">
          <p className="eyebrow">Segar setiap hari</p>
          <h1 className="mt-5 max-w-[12ch] font-display text-[clamp(3.1rem,7vw,6.2rem)] font-medium leading-[0.91] tracking-[-0.065em]">
            Bahan segar, masak lebih mudah.
          </h1>
          <p className="mt-7 max-w-md text-base leading-7 text-muted-foreground sm:text-lg">
            Ayam, ikan, sayur, dan bumbu pilihan yang dikemas bersih untuk
            kebutuhan dapur sehari-hari.
          </p>
          <div className="mt-8 flex flex-wrap items-center gap-3">
            <Button
              size="lg"
              render={<Link href="/products" />}
              className="h-11 rounded-full px-5"
            >
              Lihat produk <ArrowRight data-icon="inline-end" />
            </Button>
            <Button
              variant="outline"
              size="lg"
              render={<Link href="#about" />}
              className="h-11 rounded-full px-5"
            >
              Cara kami menjaga kesegaran
            </Button>
          </div>
          <div className="mt-10 flex items-center gap-3 border-t border-ink/10 pt-5 text-sm text-muted-foreground">
            <div className="flex text-brand" aria-label="Dinilai 4,9 dari 5">
              {[0, 1, 2, 3, 4].map((star) => (
                <Star key={star} className="size-3.5 fill-current" />
              ))}
            </div>
            <span>4,9 dari pelanggan yang rutin masak di rumah</span>
          </div>
        </div>

        <div className="relative aspect-[4/4.6] max-h-[690px] overflow-hidden rounded-[1.25rem] bg-sand">
          <div
            className="absolute inset-0 bg-[url('/uploads/products/novastra-fresh-collection.webp')] bg-cover bg-center transition-transform duration-700 hover:scale-[1.015]"
            aria-hidden="true"
          />
          <div className="absolute inset-x-0 bottom-0 flex items-end justify-between bg-gradient-to-t from-black/45 to-transparent p-5 pt-24 text-white sm:p-7">
            <div>
              <p className="text-xs uppercase tracking-[0.18em] text-white/75">
                Pilihan hari ini
              </p>
              <p className="mt-1 font-display text-2xl font-medium">
                Dari bahan segar ke meja makan
              </p>
            </div>
            <span className="grid size-10 place-items-center rounded-full bg-white text-ink">
              <ArrowRight className="size-4" />
            </span>
          </div>
        </div>
      </section>

      <section className="border-y border-ink/10 bg-white py-20 sm:py-24">
        <div className="page-shell">
          <div className="flex items-end justify-between gap-6">
            <div>
              <p className="eyebrow">Baru datang</p>
              <h2 className="section-title mt-3">Segar dan siap diolah.</h2>
            </div>
            <Link
              href="/products"
              className="hidden items-center gap-1 text-sm font-medium sm:flex"
            >
              Lihat semua <ChevronRight className="size-4" />
            </Link>
          </div>
          <div className="mt-10 grid grid-cols-2 gap-x-3 gap-y-9 lg:grid-cols-4 lg:gap-5">
            {products.map((product) => (
              <article key={product.name} className="group">
                <Link href={`/products/${product.slug}`} className="block">
                  <div className="relative aspect-square overflow-hidden rounded-xl bg-sand">
                    <div
                      className="absolute inset-0 bg-[url('/uploads/products/novastra-fresh-collection.webp')] bg-[length:200%_200%] transition-transform duration-500 group-hover:scale-[1.035]"
                      style={{ backgroundPosition: product.position }}
                      aria-hidden="true"
                    />
                    <Badge
                      variant="secondary"
                      className="absolute left-3 top-3 h-6 bg-white/90 text-[10px] backdrop-blur-sm"
                    >
                      {product.badge}
                    </Badge>
                    <span className="absolute bottom-3 right-3 grid size-9 translate-y-2 place-items-center rounded-full bg-ink text-white opacity-0 transition-all group-hover:translate-y-0 group-hover:opacity-100">
                      <ShoppingBag className="size-4" />
                    </span>
                  </div>
                  <div className="mt-4 flex items-start justify-between gap-3">
                    <div>
                      <p className="font-medium tracking-tight">
                        {product.name}
                      </p>
                      <p className="mt-1 text-xs text-muted-foreground">
                        {product.category}
                      </p>
                    </div>
                    <p className="text-sm font-medium">{product.price}</p>
                  </div>
                </Link>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section id="categories" className="page-shell py-20 sm:py-28">
        <div className="grid gap-10 md:grid-cols-[0.65fr_1.35fr] md:gap-20">
          <div>
            <p className="eyebrow">Belanja berdasarkan kebutuhan</p>
            <h2 className="section-title mt-3 max-w-[10ch]">
              Isi dapur tanpa repot.
            </h2>
          </div>
          <div className="border-t border-ink/15">
            {categories.map((category) => (
              <Link
                key={category.number}
                href={`/categories/${category.slug}`}
                className="group grid grid-cols-[3rem_1fr_auto] items-center border-b border-ink/15 py-6 sm:py-8"
              >
                <span className="text-xs text-muted-foreground">
                  {category.number}
                </span>
                <span className="font-display text-2xl font-medium tracking-tight sm:text-3xl">
                  {category.name}
                </span>
                <span className="flex items-center gap-3 text-xs text-muted-foreground">
                  <span className="hidden sm:inline">{category.count}</span>
                  <ArrowRight className="size-4 transition-transform group-hover:translate-x-1" />
                </span>
              </Link>
            ))}
          </div>
        </div>
      </section>

      <section id="about" className="bg-ink py-20 text-paper sm:py-28">
        <div className="page-shell grid gap-10 md:grid-cols-2 md:items-end">
          <p className="eyebrow text-paper/55">Tentang Novastra</p>
          <div>
            <h2 className="font-display text-4xl font-medium leading-[1.05] tracking-[-0.045em] sm:text-5xl">
              Kami pilih yang segar, agar Anda tinggal memasak.
            </h2>
            <p className="mt-6 max-w-xl leading-7 text-paper/65">
              Novastra membantu keluarga mendapatkan bahan masak yang segar dan
              jelas penanganannya. Kami memilih, menyimpan dingin, dan mengemas
              seperlunya agar bahan sampai dalam kondisi baik.
            </p>
          </div>
        </div>
      </section>

      <footer id="contact" className="bg-[#0f2f20] py-12 text-paper">
        <div className="page-shell grid gap-10 border-b border-white/10 pb-10 sm:grid-cols-2 lg:grid-cols-4">
          <div>
            <p className="font-display text-xl font-semibold">novastra</p>
            <p className="mt-3 max-w-xs text-sm leading-6 text-paper/55">
              Bahan segar untuk masak sehari-hari.
            </p>
          </div>
          <div>
            <p className="footer-title">Belanja</p>
            <div className="footer-links">
              <Link href="/products">Semua produk</Link>
              <Link href="#categories">Kategori</Link>
              <Link href="/orders">Pesanan saya</Link>
            </div>
          </div>
          <div>
            <p className="footer-title">Perusahaan</p>
            <div className="footer-links">
              <Link href="#about">Tentang kami</Link>
              <Link href="#contact">Kontak</Link>
            </div>
          </div>
          <div>
            <p className="footer-title">Hubungi</p>
            <div className="footer-links">
              <a href="mailto:halo@novastra.id">halo@novastra.id</a>
              <a href="https://wa.me/6281234567890">WhatsApp</a>
              <span>Senin–Sabtu, 09.00–17.00</span>
            </div>
          </div>
        </div>
        <div className="page-shell flex flex-col gap-2 pt-6 text-xs text-paper/40 sm:flex-row sm:justify-between">
          <span>© 2026 Novastra. Hak cipta dilindungi.</span>
          <span>Pengiriman dari Indonesia.</span>
        </div>
      </footer>
    </main>
  );
}
