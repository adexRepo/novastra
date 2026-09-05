import { AdminPageHeader } from '@/components/admin/page-header';
import { Badge } from '@/components/ui/badge';
import { prisma } from '@/lib/db/prisma';

export default async function CategoriesPage() {
  const categories = await prisma.category.findMany({
    include: { _count: { select: { products: true } } },
    orderBy: { name: 'asc' },
  });
  return (
    <>
      <AdminPageHeader
        eyebrow="Katalog"
        title="Kategori"
        description="Kelompokkan produk tanpa membuat navigasi terlalu rumit."
      />
      <div className="mt-7 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        {categories.map((category) => (
          <article
            key={category.id}
            className="rounded-xl border border-ink/10 bg-white p-5"
          >
            <div className="flex items-start justify-between">
              <div>
                <h2 className="font-display text-xl font-medium">
                  {category.name}
                </h2>
                <p className="mt-1 text-xs text-muted-foreground">
                  /{category.slug}
                </p>
              </div>
              <Badge
                variant={category.status === 'ACTIVE' ? 'secondary' : 'outline'}
              >
                {category.status === 'ACTIVE' ? 'Aktif' : 'Nonaktif'}
              </Badge>
            </div>
            <p className="mt-5 text-sm text-muted-foreground">
              {category._count.products} produk
            </p>
          </article>
        ))}
        {categories.length === 0 && (
          <p className="text-sm text-muted-foreground">Belum ada kategori.</p>
        )}
      </div>
    </>
  );
}
