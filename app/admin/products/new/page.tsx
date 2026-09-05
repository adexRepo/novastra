import Link from 'next/link';
import { ArrowLeft } from 'lucide-react';
import { ProductForm } from '@/components/admin/product-form';
import { prisma } from '@/lib/db/prisma';

export default async function NewProductPage() {
  const categories = await prisma.category.findMany({
    where: { status: 'ACTIVE' },
    orderBy: { name: 'asc' },
    select: { id: true, name: true },
  });
  return (
    <>
      <Link
        href="/admin/products"
        className="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-ink"
      >
        <ArrowLeft className="size-4" />
        Kembali
      </Link>
      <h1 className="mt-5 font-display text-3xl font-medium tracking-tight">
        Produk baru
      </h1>
      <p className="mt-2 text-sm text-muted-foreground">
        Isi informasi yang benar-benar dibutuhkan pelanggan.
      </p>
      <ProductForm categories={categories} />
    </>
  );
}
