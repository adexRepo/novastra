import type { Metadata } from 'next';
import { Geist, Libre_Franklin } from 'next/font/google';
import './globals.css';
import { PageViewTracker } from '@/components/analytics/page-view-tracker';
import { CartProvider } from '@/components/storefront/cart-provider';

const geist = Geist({ variable: '--font-geist-sans', subsets: ['latin'] });
const display = Libre_Franklin({
  variable: '--font-display',
  subsets: ['latin'],
});

export const metadata: Metadata = {
  metadataBase: new URL(
    process.env.NEXT_PUBLIC_APP_URL ?? 'http://localhost:3000',
  ),
  title: {
    default: 'Novastra — Bahan segar untuk masak sehari-hari',
    template: '%s · Novastra',
  },
  description:
    'Belanja ayam, ikan, sayur, dan bumbu segar yang dikemas bersih untuk dapur Anda.',
  openGraph: {
    title: 'Novastra — Bahan segar untuk masak sehari-hari',
    description:
      'Belanja ayam, ikan, sayur, dan bumbu segar yang dikemas bersih untuk dapur Anda.',
    type: 'website',
    images: [
      {
        url: '/og.webp',
        width: 1736,
        height: 909,
        alt: 'Novastra — Bahan segar, masak lebih mudah.',
      },
    ],
  },
  twitter: {
    card: 'summary_large_image',
    title: 'Novastra — Bahan segar untuk masak sehari-hari',
    description: 'Ayam, ikan, sayur, dan bumbu segar untuk dapur Anda.',
    images: ['/og.webp'],
  },
};

export default function RootLayout({
  children,
}: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="id">
      <body className={`${geist.variable} ${display.variable} antialiased`}>
        <CartProvider>
          <PageViewTracker />
          {children}
        </CartProvider>
      </body>
    </html>
  );
}
