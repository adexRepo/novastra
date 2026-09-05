import { notFound } from 'next/navigation';
import { ProductCard } from '@/components/storefront/product-card';
import { SiteFooter } from '@/components/storefront/site-footer';
import { SiteHeader } from '@/components/storefront/site-header';
import { categories, products } from '@/lib/catalog';

export function generateStaticParams() {
  return categories.map(({ slug }) => ({ slug }));
}
export default async function CategoryPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const slug = (await params).slug;
  const category = categories.find((item) => item.slug === slug);
  if (!category) notFound();
  const items = products.filter((product) => product.categorySlug === slug);
  return (
    <main>
      <SiteHeader />
      <section className="page-shell min-h-[65vh] py-14 sm:py-20">
        <p className="eyebrow">Kategori</p>
        <h1 className="section-title mt-3">{category.name}</h1>
        <p className="mt-4 text-sm text-muted-foreground">
          {items.length} produk dipilih untuk kategori ini.
        </p>
        <div className="mt-10 grid grid-cols-2 gap-x-3 gap-y-10 lg:grid-cols-4 lg:gap-5">
          {items.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      </section>
      <SiteFooter />
    </main>
  );
}
