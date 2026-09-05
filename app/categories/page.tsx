import type { Metadata } from 'next';
import Link from 'next/link';
import { ArrowRight } from 'lucide-react';
import { SiteFooter } from '@/components/storefront/site-footer';
import { SiteHeader } from '@/components/storefront/site-header';
import { categories, products } from '@/lib/catalog';

export const metadata: Metadata = { title: 'Kategori' };
export default function CategoriesPage() {
  return (
    <main>
      <SiteHeader />
      <section className="page-shell min-h-[65vh] py-14 sm:py-20">
        <p className="eyebrow">Kategori</p>
        <h1 className="section-title mt-3">Temukan berdasarkan kebutuhan.</h1>
        <div className="mt-12 grid gap-4 sm:grid-cols-2">
          {categories.map((category, index) => (
            <Link
              key={category.slug}
              href={`/categories/${category.slug}`}
              className="group relative min-h-64 overflow-hidden rounded-2xl bg-sand p-6 sm:p-8"
            >
              <div
                className="absolute inset-0 bg-[url('/uploads/products/novastra-fresh-collection.webp')] bg-[length:200%_200%] opacity-70 transition-transform duration-500 group-hover:scale-[1.03]"
                style={{ backgroundPosition: products[index].position }}
                aria-hidden="true"
              />
              <div className="absolute inset-0 bg-gradient-to-t from-ink/75 via-transparent to-transparent" />
              <div className="absolute inset-x-0 bottom-0 flex items-end justify-between p-6 text-white sm:p-8">
                <div>
                  <p className="text-xs uppercase tracking-widest text-white/60">
                    {
                      products.filter(
                        (item) => item.categorySlug === category.slug,
                      ).length
                    }{' '}
                    produk
                  </p>
                  <h2 className="mt-2 font-display text-3xl font-medium">
                    {category.name}
                  </h2>
                </div>
                <span className="grid size-10 place-items-center rounded-full bg-white text-ink">
                  <ArrowRight className="size-4" />
                </span>
              </div>
            </Link>
          ))}
        </div>
      </section>
      <SiteFooter />
    </main>
  );
}
