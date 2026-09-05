'use client';

import Link from 'next/link';
import { Plus, ShoppingBag } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatRupiah, type Product } from '@/lib/catalog';
import { useCart } from './cart-provider';
import { ProductImage } from './product-image';

export function ProductCard({ product }: { product: Product }) {
  const { addItem } = useCart();
  return (
    <article className="group min-w-0">
      <div className="relative aspect-square overflow-hidden rounded-xl bg-sand">
        <Link
          href={`/products/${product.slug}`}
          aria-label={`Lihat ${product.name}`}
        >
          <ProductImage
            position={product.position}
            name={product.name}
            className="transition-transform duration-500 group-hover:scale-[1.035]"
          />
        </Link>
        {product.stock <= 5 && (
          <Badge
            variant={product.stock === 0 ? 'outline' : 'secondary'}
            className="absolute left-3 top-3 bg-white/90"
          >
            {product.stock === 0 ? 'Habis' : `${product.stock} tersisa`}
          </Badge>
        )}
        <Button
          size="icon"
          disabled={product.stock === 0}
          aria-label={`Tambah ${product.name} ke keranjang`}
          onClick={() => addItem(product)}
          className="absolute bottom-3 right-3 translate-y-2 rounded-full opacity-0 transition-all group-hover:translate-y-0 group-hover:opacity-100 focus-visible:translate-y-0 focus-visible:opacity-100"
        >
          <Plus />
        </Button>
      </div>
      <div className="mt-4 flex items-start justify-between gap-3">
        <div className="min-w-0">
          <Link
            href={`/products/${product.slug}`}
            className="font-medium tracking-tight hover:underline"
          >
            {product.name}
          </Link>
          <p className="mt-1 text-xs text-muted-foreground">
            {product.category}
          </p>
        </div>
        <p className="shrink-0 text-sm font-medium">
          {formatRupiah(product.price)}
        </p>
      </div>
      <div className="mt-3 sm:hidden">
        <Button
          variant="outline"
          size="sm"
          className="w-full"
          disabled={product.stock === 0}
          onClick={() => addItem(product)}
        >
          <ShoppingBag />
          {product.stock === 0 ? 'Stok habis' : 'Tambah'}
        </Button>
      </div>
    </article>
  );
}
