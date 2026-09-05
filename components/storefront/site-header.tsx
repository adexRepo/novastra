'use client';

import Link from 'next/link';
import { CircleUserRound, Menu, Search, ShoppingBag } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet';
import { useCart } from './cart-provider';

export function SiteHeader() {
  const { count } = useCart();
  return (
    <>
      <div className="border-b border-ink/10 bg-ink px-4 py-2 text-center text-xs tracking-wide text-paper">
        Gratis pengiriman untuk pesanan di atas Rp250.000
      </div>
      <header className="sticky top-0 z-40 border-b border-ink/10 bg-paper/95 backdrop-blur-md">
        <div className="page-shell flex h-16 items-center justify-between gap-5">
          <Link
            href="/"
            className="flex items-center gap-2.5"
            aria-label="Novastra, beranda"
          >
            <span className="grid size-7 place-items-center rounded-full bg-brand text-[11px] font-bold text-white">
              N
            </span>
            <span className="font-display text-lg font-semibold tracking-[-0.03em]">
              novastra
            </span>
          </Link>
          <nav
            className="hidden items-center gap-7 md:flex"
            aria-label="Navigasi utama"
          >
            <Link href="/products" className="nav-link">
              Produk
            </Link>
            <Link href="/categories" className="nav-link">
              Kategori
            </Link>
            <Link href="/about" className="nav-link">
              Tentang
            </Link>
            <Link href="/contact" className="nav-link">
              Kontak
            </Link>
          </nav>
          <div className="flex items-center gap-1">
            <Button
              variant="ghost"
              size="icon"
              render={<Link href="/products" />}
              aria-label="Cari produk"
            >
              <Search />
            </Button>
            <Button
              variant="ghost"
              size="icon"
              render={<Link href="/orders" />}
              aria-label="Akun"
              className="hidden sm:inline-flex"
            >
              <CircleUserRound />
            </Button>
            <Button
              variant="ghost"
              size="icon"
              render={<Link href="/cart" />}
              aria-label={`Keranjang, ${count} barang`}
              className="relative"
            >
              <ShoppingBag />
              {count > 0 && (
                <span className="absolute right-0 top-0 grid size-4 place-items-center rounded-full bg-brand text-[9px] font-bold text-white">
                  {count > 9 ? '9+' : count}
                </span>
              )}
            </Button>
            <Sheet>
              <SheetTrigger
                render={
                  <Button
                    variant="ghost"
                    size="icon"
                    aria-label="Buka menu"
                    className="md:hidden"
                  />
                }
              >
                <Menu />
              </SheetTrigger>
              <SheetContent className="bg-paper">
                <SheetHeader>
                  <SheetTitle className="font-display text-xl">Menu</SheetTitle>
                </SheetHeader>
                <nav
                  className="flex flex-col border-t border-ink/10 px-4"
                  aria-label="Navigasi mobile"
                >
                  {[
                    ['Produk', '/products'],
                    ['Kategori', '/categories'],
                    ['Tentang', '/about'],
                    ['Kontak', '/contact'],
                    ['Pesanan saya', '/orders'],
                  ].map(([label, href]) => (
                    <Link
                      key={href}
                      href={href}
                      className="border-b border-ink/10 py-4 font-display text-xl font-medium"
                    >
                      {label}
                    </Link>
                  ))}
                </nav>
              </SheetContent>
            </Sheet>
          </div>
        </div>
      </header>
    </>
  );
}
