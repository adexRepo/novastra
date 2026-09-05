import type { Metadata } from 'next';
import Link from 'next/link';
import { redirect } from 'next/navigation';
import { ArrowLeft, Check, Leaf, ShieldCheck } from 'lucide-react';
import { auth } from '@/auth';
import { CustomerLoginForm } from '@/components/storefront/customer-login-form';
import { SiteFooter } from '@/components/storefront/site-footer';
import { SiteHeader } from '@/components/storefront/site-header';
import { safeCallbackUrl } from '@/lib/auth/callback-url';
import { getCustomerCredentialConfig } from '@/lib/auth/customer-credentials';

export const metadata: Metadata = { title: 'Masuk Pelanggan' };
export const dynamic = 'force-dynamic';

type LoginPageProps = {
  searchParams: Promise<{
    callbackUrl?: string | string[];
  }>;
};

export default async function LoginPage({ searchParams }: LoginPageProps) {
  const query = await searchParams;
  const callbackUrl = safeCallbackUrl(
    Array.isArray(query.callbackUrl) ? query.callbackUrl[0] : query.callbackUrl,
  );
  const session = await auth();

  if (session?.user?.role === 'CUSTOMER') redirect(callbackUrl);
  const { usesDevelopmentDefault } = getCustomerCredentialConfig();

  return (
    <main className="min-h-screen bg-paper">
      <SiteHeader />
      <section className="page-shell py-8 sm:py-12 lg:py-16">
        <Link
          href={callbackUrl === '/checkout' ? '/cart' : '/'}
          className="inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-ink"
        >
          <ArrowLeft className="size-4" />
          {callbackUrl === '/checkout'
            ? 'Kembali ke keranjang'
            : 'Kembali ke toko'}
        </Link>

        <div className="mx-auto mt-7 grid max-w-5xl overflow-hidden rounded-3xl border border-ink/10 bg-white shadow-[0_20px_70px_rgb(24_51_38/10%)] lg:grid-cols-[1.05fr_0.95fr]">
          <div className="relative min-h-56 overflow-hidden bg-ink sm:min-h-72 lg:min-h-[590px]">
            <div className="absolute inset-0 bg-[url('/uploads/products/novastra-fresh-collection.webp')] bg-cover bg-center opacity-75" />
            <div className="absolute inset-0 bg-gradient-to-t from-ink via-ink/25 to-transparent" />
            <div className="absolute inset-x-0 bottom-0 p-6 text-white sm:p-9 lg:p-12">
              <span className="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3 py-1.5 text-xs font-medium backdrop-blur-sm">
                <Leaf className="size-3.5" />
                Segar setiap hari
              </span>
              <h1 className="mt-5 max-w-md font-display text-3xl font-medium leading-tight tracking-[-0.04em] sm:text-4xl">
                Satu akun untuk belanja dan melacak pesanan.
              </h1>
            </div>
          </div>

          <div className="flex items-center px-5 py-9 sm:px-10 sm:py-12 lg:px-12">
            <div className="w-full">
              <div className="flex items-center gap-2.5">
                <span className="grid size-8 place-items-center rounded-full bg-brand text-xs font-bold text-white">
                  N
                </span>
                <span className="font-display text-xl font-semibold tracking-[-0.03em]">
                  novastra
                </span>
              </div>
              <p className="eyebrow mt-10">Akun pelanggan</p>
              <h2 className="mt-3 font-display text-3xl font-medium tracking-[-0.04em] sm:text-4xl">
                Masuk untuk melanjutkan.
              </h2>
              <p className="mt-3 max-w-sm text-sm leading-6 text-muted-foreground">
                Gunakan username dan password pelanggan sementara untuk checkout
                dan melihat riwayat pesanan.
              </p>

              <CustomerLoginForm
                callbackUrl={callbackUrl}
                showDevelopmentCredentials={usesDevelopmentDefault}
              />

              <div className="mt-8 space-y-3 border-t border-ink/10 pt-6 text-sm text-muted-foreground">
                <p className="flex items-start gap-3">
                  <ShieldCheck className="mt-0.5 size-4 shrink-0 text-brand" />
                  Sesi ditandatangani oleh Auth.js di server.
                </p>
                <p className="flex items-start gap-3">
                  <Check className="mt-0.5 size-4 shrink-0 text-brand" />
                  Akun pelanggan dan admin tetap dipisahkan.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>
      <SiteFooter />
    </main>
  );
}
