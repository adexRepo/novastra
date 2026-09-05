'use client';

import { useState } from 'react';
import { Check, Minus, Plus, ShoppingBag } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { Product } from '@/lib/catalog';
import { useCart } from './cart-provider';

export function AddToCart({ product }: { product: Product }) {
  const [quantity, setQuantity] = useState(1);
  const [added, setAdded] = useState(false);
  const { addItem } = useCart();
  const add = () => {
    addItem(product, quantity);
    setAdded(true);
    window.setTimeout(() => setAdded(false), 1800);
  };
  return (
    <div className="mt-8 flex flex-col gap-3 sm:flex-row">
      <div className="flex h-11 items-center justify-between rounded-full border border-ink/15 bg-white px-1">
        <Button
          variant="ghost"
          size="icon"
          className="rounded-full"
          onClick={() => setQuantity((value) => Math.max(1, value - 1))}
          aria-label="Kurangi jumlah"
        >
          <Minus />
        </Button>
        <span
          className="w-10 text-center text-sm font-medium"
          aria-live="polite"
        >
          {quantity}
        </span>
        <Button
          variant="ghost"
          size="icon"
          className="rounded-full"
          onClick={() =>
            setQuantity((value) => Math.min(product.stock, value + 1))
          }
          aria-label="Tambah jumlah"
        >
          <Plus />
        </Button>
      </div>
      <Button
        size="lg"
        className="h-11 flex-1 rounded-full"
        onClick={add}
        disabled={product.stock === 0}
      >
        {added ? (
          <>
            <Check />
            Sudah ditambahkan
          </>
        ) : (
          <>
            <ShoppingBag />
            {product.stock === 0 ? 'Stok habis' : 'Tambah ke keranjang'}
          </>
        )}
      </Button>
    </div>
  );
}
