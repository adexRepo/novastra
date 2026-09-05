import Link from 'next/link';
import { Plus } from 'lucide-react';
import { AdminPageHeader } from '@/components/admin/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { prisma } from '@/lib/db/prisma';
import { formatRupiah } from '@/lib/catalog';

export default async function AdminProductsPage() {
  const products = await prisma.product.findMany({
    include: { category: true },
    orderBy: { updatedAt: 'desc' },
  });
  return (
    <>
      <AdminPageHeader
        eyebrow="Katalog"
        title="Produk"
        description="Kelola harga, stok, status, dan gambar produk."
        action={
          <Button render={<Link href="/admin/products/new" />}>
            <Plus />
            Produk baru
          </Button>
        }
      />
      <div className="mt-7 overflow-hidden rounded-xl border border-ink/10 bg-white">
        <div className="overflow-x-auto">
          <table className="w-full min-w-[760px] text-left text-sm">
            <thead className="bg-sand/50 text-xs text-muted-foreground">
              <tr>
                <th className="px-5 py-3 font-medium">Produk</th>
                <th className="px-5 py-3 font-medium">Kategori</th>
                <th className="px-5 py-3 font-medium">Harga</th>
                <th className="px-5 py-3 font-medium">Stok</th>
                <th className="px-5 py-3 font-medium">Status</th>
                <th className="px-5 py-3 text-right font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {products.map((product) => (
                <tr key={product.id} className="border-t border-ink/10">
                  <td className="px-5 py-4">
                    <p className="font-medium">{product.name}</p>
                    <p className="mt-0.5 text-xs text-muted-foreground">
                      {product.sku}
                    </p>
                  </td>
                  <td className="px-5 py-4">{product.category.name}</td>
                  <td className="px-5 py-4">
                    {formatRupiah(product.price.toNumber())}
                  </td>
                  <td className="px-5 py-4">
                    <span
                      className={
                        product.stock <= 5 ? 'font-semibold text-amber-700' : ''
                      }
                    >
                      {product.stock}
                    </span>
                  </td>
                  <td className="px-5 py-4">
                    <Badge
                      variant={
                        product.status === 'ACTIVE' ? 'secondary' : 'outline'
                      }
                    >
                      {product.status === 'ACTIVE' ? 'Aktif' : 'Nonaktif'}
                    </Badge>
                  </td>
                  <td className="px-5 py-4 text-right">
                    <Button
                      variant="ghost"
                      size="sm"
                      render={<Link href={`/admin/products/${product.id}`} />}
                    >
                      Edit
                    </Button>
                  </td>
                </tr>
              ))}
              {products.length === 0 && (
                <tr>
                  <td
                    colSpan={6}
                    className="px-5 py-16 text-center text-muted-foreground"
                  >
                    Belum ada produk.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </>
  );
}
