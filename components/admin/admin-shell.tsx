'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import {
  Boxes,
  ChevronRight,
  ClipboardList,
  CreditCard,
  LayoutDashboard,
  LogOut,
  Menu,
  MessageSquareText,
  PackagePlus,
  Tags,
  TableProperties,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
  Sheet,
  SheetClose,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet';
import { cn } from '@/lib/utils';

const links = [
  { href: '/admin', label: 'Ringkasan', icon: LayoutDashboard },
  { href: '/admin/products', label: 'Produk', icon: Boxes },
  { href: '/admin/categories', label: 'Kategori', icon: Tags },
  { href: '/admin/orders', label: 'Pesanan', icon: ClipboardList },
  { href: '/admin/payments', label: 'Pembayaran', icon: CreditCard },
  { href: '/admin/feedback', label: 'Feedback', icon: MessageSquareText },
  { href: '/admin/reports', label: 'Laporan', icon: TableProperties },
];

export function AdminShell({
  children,
  adminName,
}: {
  children: React.ReactNode;
  adminName: string;
}) {
  const pathname = usePathname();
  if (pathname === '/admin/login') return <>{children}</>;
  return (
    <div className="min-h-screen bg-[#f3f1ec] lg:grid lg:grid-cols-[240px_1fr]">
      <aside className="hidden border-r border-ink/10 bg-ink text-paper lg:flex lg:flex-col">
        <div className="flex h-20 items-center gap-2.5 border-b border-white/10 px-6">
          <span className="grid size-7 place-items-center rounded-full bg-brand text-[11px] font-bold">
            N
          </span>
          <span className="font-display text-lg font-semibold">novastra</span>
        </div>
        <nav className="flex-1 space-y-1 p-3" aria-label="Navigasi admin">
          {links.map(({ href, label, icon: Icon }) => {
            const active =
              href === '/admin' ? pathname === href : pathname.startsWith(href);
            return (
              <Link
                key={href}
                href={href}
                className={cn(
                  'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-paper/60 transition-colors hover:bg-white/5 hover:text-paper',
                  active && 'bg-white/10 text-paper',
                )}
              >
                <Icon className="size-4" />
                {label}
                {active && <ChevronRight className="ml-auto size-3.5" />}
              </Link>
            );
          })}
        </nav>
        <div className="border-t border-white/10 p-4">
          <p className="px-2 text-xs text-paper/40">Masuk sebagai</p>
          <p className="mt-1 truncate px-2 text-sm">{adminName}</p>
          <Button
            variant="ghost"
            className="mt-3 w-full justify-start text-paper/55 hover:bg-white/5 hover:text-paper"
          >
            <LogOut />
            Keluar
          </Button>
        </div>
      </aside>
      <div className="min-w-0">
        <header className="flex h-16 items-center justify-between border-b border-ink/10 bg-white px-4 sm:px-7">
          <Sheet>
            <SheetTrigger
              render={
                <Button
                  variant="ghost"
                  size="icon"
                  className="lg:hidden"
                  aria-label="Buka menu admin"
                />
              }
            >
              <Menu />
            </SheetTrigger>
            <SheetContent side="left" className="bg-ink text-paper">
              <SheetHeader className="border-b border-white/10 px-5 py-5">
                <SheetTitle className="flex items-center gap-2.5 font-display text-lg text-paper">
                  <span className="grid size-7 place-items-center rounded-full bg-brand text-[11px] font-bold">
                    N
                  </span>
                  novastra admin
                </SheetTitle>
              </SheetHeader>
              <nav className="space-y-1 p-3" aria-label="Navigasi admin mobile">
                {links.map(({ href, label, icon: Icon }) => {
                  const active =
                    href === '/admin'
                      ? pathname === href
                      : pathname.startsWith(href);
                  return (
                    <SheetClose
                      key={href}
                      render={
                        <Link
                          href={href}
                          className={cn(
                            'flex items-center gap-3 rounded-lg px-3 py-3 text-sm text-paper/65 transition-colors hover:bg-white/5 hover:text-paper',
                            active && 'bg-white/10 text-paper',
                          )}
                        />
                      }
                    >
                      <Icon className="size-4" />
                      {label}
                    </SheetClose>
                  );
                })}
              </nav>
            </SheetContent>
          </Sheet>
          <div className="ml-auto flex items-center gap-3">
            <Button render={<Link href="/admin/products/new" />} size="sm">
              <PackagePlus />
              Produk baru
            </Button>
            <span className="grid size-8 place-items-center rounded-full bg-sand text-xs font-semibold">
              {adminName.slice(0, 1).toUpperCase()}
            </span>
          </div>
        </header>
        <main className="p-4 sm:p-7 lg:p-9">{children}</main>
      </div>
    </div>
  );
}
