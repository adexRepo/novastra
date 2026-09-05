import type { Metadata } from 'next';
import Link from 'next/link';
import { ArrowLeft } from 'lucide-react';
import { AdminLoginForm } from '@/components/admin/login-form';

export const metadata: Metadata = { title: 'Login Admin' };
export default function AdminLoginPage() {
  return (
    <main className="grid min-h-screen bg-[#edf5e9] lg:grid-cols-2">
      <section className="flex min-h-screen items-center justify-center px-5 py-12">
        <div className="w-full max-w-sm">
          <Link
            href="/"
            className="mb-12 inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-ink"
          >
            <ArrowLeft className="size-4" />
            Kembali ke toko
          </Link>
          <div className="flex items-center gap-2.5">
            <span className="grid size-8 place-items-center rounded-full bg-brand text-xs font-bold text-white">
              N
            </span>
            <span className="font-display text-xl font-semibold">novastra</span>
            <span className="rounded-full bg-ink/5 px-2 py-1 text-[10px] font-semibold uppercase tracking-wider">
              admin
            </span>
          </div>
          <p className="eyebrow mt-12">Area terbatas</p>
          <h1 className="mt-3 font-display text-4xl font-medium tracking-[-0.045em]">
            Selamat datang kembali.
          </h1>
          <p className="mt-3 text-sm leading-6 text-muted-foreground">
            Masuk dengan akun admin yang telah dikonfigurasi oleh pemilik toko.
          </p>
          <AdminLoginForm />
          <p className="mt-8 text-xs leading-5 text-muted-foreground">
            Percobaan masuk dibatasi. Kredensial tidak pernah dicatat atau
            dikirim ke klien.
          </p>
        </div>
      </section>
      <aside className="relative hidden overflow-hidden bg-ink lg:block">
        <div className="absolute inset-0 bg-[url('/uploads/products/novastra-fresh-collection.webp')] bg-cover bg-center opacity-80" />
        <div className="absolute inset-0 bg-gradient-to-t from-ink via-ink/10 to-transparent" />
        <blockquote className="absolute bottom-16 left-16 max-w-lg font-display text-3xl font-medium leading-tight tracking-tight text-white">
          “Kelola yang penting. Sisanya biarkan tetap sederhana.”
        </blockquote>
      </aside>
    </main>
  );
}
