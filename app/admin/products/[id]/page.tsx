import Link from 'next/link';
import { notFound } from 'next/navigation';
import { ArrowLeft } from 'lucide-react';
import { toggleProductStatus } from './actions';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { prisma } from '@/lib/db/prisma';
import { formatRupiah } from '@/lib/catalog';

export default async function AdminProductDetail({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const product = await prisma.product.findUnique({
    where: { id: (await params).id },
    include: { category: true },
  });
  if (!product) notFound();
  return (
    <>
      <Link
        href="/admin/products"
        className="inline-flex items-center gap-2 text-sm text-muted-foreground"
      >
        <ArrowLeft className="size-4" />
        Semua produk
      </Link>
      <div className="mt-6 max-w-3xl rounded-xl border border-ink/10 bg-white p-6">
        <div className="flex items-start justify-between gap-4">
          <div>
            <p className="eyebrow">{product.category.name}</p>
            <h1 className="mt-2 font-display text-3xl font-medium">
              {product.name}
            </h1>
            <p className="mt-1 text-sm text-muted-foreground">{product.sku}</p>
          </div>
          <Badge
            variant={product.status === 'ACTIVE' ? 'secondary' : 'outline'}
          >
            {product.status}
          </Badge>
        </div>
        <dl className="mt-7 grid gap-5 border-y border-ink/10 py-6 sm:grid-cols-3">
          <div>
            <dt className="text-xs text-muted-foreground">Harga</dt>
            <dd className="mt-1 font-medium">
              {formatRupiah(product.price.toNumber())}
            </dd>
          </div>
          <div>
            <dt className="text-xs text-muted-foreground">Stok</dt>
            <dd className="mt-1 font-medium">{product.stock}</dd>
          </div>
          <div>
            <dt className="text-xs text-muted-foreground">Unggulan</dt>
            <dd className="mt-1 font-medium">
              {product.featured ? 'Ya' : 'Tidak'}
            </dd>
          </div>
        </dl>
        <p className="mt-6 text-sm leading-7 text-muted-foreground">
          {product.description}
        </p>
        <form action={toggleProductStatus} className="mt-7">
          <input type="hidden" name="id" value={product.id} />
          <Button
            type="submit"
            variant={product.status === 'ACTIVE' ? 'destructive' : 'default'}
          >
            {product.status === 'ACTIVE'
              ? 'Nonaktifkan produk'
              : 'Aktifkan produk'}
          </Button>
        </form>
      </div>
    </>
  );
}
