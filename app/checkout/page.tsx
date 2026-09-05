import type { Metadata } from 'next';
import { redirect } from 'next/navigation';
import { auth } from '@/auth';
import { CheckoutForm } from '@/components/storefront/checkout-form';
import { SiteHeader } from '@/components/storefront/site-header';

export const metadata: Metadata = { title: 'Checkout' };
export const dynamic = 'force-dynamic';

export default async function CheckoutPage() {
  const session = await auth();

  if (!session?.user || session.user.role !== 'CUSTOMER') {
    redirect('/login?callbackUrl=%2Fcheckout');
  }

  return (
    <main>
      <SiteHeader />
      <CheckoutForm
        customerName={session.user.name ?? ''}
        customerEmail={session.user.email ?? ''}
      />
    </main>
  );
}
