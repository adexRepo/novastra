'use client';

import { useMemo, useState } from 'react';
import { Search, SlidersHorizontal, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  NativeSelect,
  NativeSelectOption,
} from '@/components/ui/native-select';
import { categories, products } from '@/lib/catalog';
import { ProductCard } from './product-card';

type Sort = 'newest' | 'price-asc' | 'price-desc' | 'name';

export function ProductsBrowser() {
  const [query, setQuery] = useState('');
  const [category, setCategory] = useState('all');
  const [inStock, setInStock] = useState(false);
  const [sort, setSort] = useState<Sort>('newest');

  const visible = useMemo(() => {
    const normalized = query.trim().toLowerCase();
    return products
      .filter(
        (product) =>
          !normalized ||
          product.name.toLowerCase().includes(normalized) ||
          product.shortDescription.toLowerCase().includes(normalized),
      )
      .filter(
        (product) => category === 'all' || product.categorySlug === category,
      )
      .filter((product) => !inStock || product.stock > 0)
      .sort((a, b) =>
        sort === 'price-asc'
          ? a.price - b.price
          : sort === 'price-desc'
            ? b.price - a.price
            : sort === 'name'
              ? a.name.localeCompare(b.name)
              : b.id.localeCompare(a.id),
      );
  }, [query, category, inStock, sort]);

  const reset = () => {
    setQuery('');
    setCategory('all');
    setInStock(false);
    setSort('newest');
  };

  return (
    <>
      <div className="mt-10 grid gap-3 border-y border-ink/10 py-4 md:grid-cols-[minmax(240px,1fr)_auto_auto] md:items-center">
        <label htmlFor="product-search" className="relative block">
          <span className="sr-only">Cari produk</span>
          <Search className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            id="product-search"
            value={query}
            onChange={(event) => setQuery(event.target.value)}
            className="h-10 rounded-full bg-white pl-10"
            placeholder="Cari nama produk..."
          />
        </label>
        <div className="flex gap-2 overflow-x-auto pb-1 md:pb-0">
          <Button
            size="sm"
            variant={category === 'all' ? 'default' : 'outline'}
            className="rounded-full"
            onClick={() => setCategory('all')}
          >
            Semua
          </Button>
          {categories.map((item) => (
            <Button
              key={item.slug}
              size="sm"
              variant={category === item.slug ? 'default' : 'outline'}
              className="rounded-full"
              onClick={() => setCategory(item.slug)}
            >
              {item.name}
            </Button>
          ))}
        </div>
        <div className="flex items-center gap-2">
          <Button
            size="sm"
            variant={inStock ? 'default' : 'outline'}
            className="rounded-full"
            onClick={() => setInStock((value) => !value)}
          >
            <SlidersHorizontal />
            Tersedia
          </Button>
          <NativeSelect
            value={sort}
            onChange={(event) => setSort(event.target.value as Sort)}
            className="h-8 min-w-36 rounded-full bg-white pl-3 text-xs"
          >
            <NativeSelectOption value="newest">Terbaru</NativeSelectOption>
            <NativeSelectOption value="price-asc">
              Harga terendah
            </NativeSelectOption>
            <NativeSelectOption value="price-desc">
              Harga tertinggi
            </NativeSelectOption>
            <NativeSelectOption value="name">Nama A–Z</NativeSelectOption>
          </NativeSelect>
        </div>
      </div>

      <div className="mt-8 flex items-center justify-between">
        <p className="text-sm text-muted-foreground">{visible.length} produk</p>
        {(query || category !== 'all' || inStock || sort !== 'newest') && (
          <Button variant="ghost" size="sm" onClick={reset}>
            <X />
            Atur ulang
          </Button>
        )}
      </div>

      {visible.length > 0 ? (
        <div className="mt-6 grid grid-cols-2 gap-x-3 gap-y-10 lg:grid-cols-4 lg:gap-x-5 lg:gap-y-12">
          {visible.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      ) : (
        <div className="my-24 text-center">
          <p className="font-display text-2xl font-medium">
            Produk tidak ditemukan.
          </p>
          <p className="mt-2 text-sm text-muted-foreground">
            Coba kata kunci atau kategori lain.
          </p>
          <Button
            variant="outline"
            className="mt-5 rounded-full"
            onClick={reset}
          >
            Atur ulang filter
          </Button>
        </div>
      )}
    </>
  );
}
