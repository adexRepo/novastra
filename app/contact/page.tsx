import type { Metadata } from 'next';
import { Mail, MessageCircle, Clock3 } from 'lucide-react';
import { ContactForm } from '@/components/storefront/contact-form';
import { SiteFooter } from '@/components/storefront/site-footer';
import { SiteHeader } from '@/components/storefront/site-header';

export const metadata: Metadata = { title: 'Kontak' };
export default function ContactPage() {
  return (
    <main>
      <SiteHeader />
      <section className="page-shell grid min-h-[65vh] gap-12 py-14 lg:grid-cols-[0.8fr_1.2fr] lg:gap-24 lg:py-20">
        <div>
          <p className="eyebrow">Kontak</p>
          <h1 className="section-title mt-3">Kami siap membantu.</h1>
          <p className="mt-5 max-w-md leading-7 text-muted-foreground">
            Punya pertanyaan tentang produk atau pesanan? Kirim pesan singkat,
            atau lanjutkan melalui WhatsApp.
          </p>
          <div className="mt-9 space-y-5 text-sm">
            <p className="flex items-center gap-3">
              <Mail className="size-4 text-brand" />
              halo@novastra.id
            </p>
            <p className="flex items-center gap-3">
              <MessageCircle className="size-4 text-brand" />
              +62 812 3456 7890
            </p>
            <p className="flex items-center gap-3">
              <Clock3 className="size-4 text-brand" />
              Senin–Sabtu, 09.00–17.00 WITA
            </p>
          </div>
        </div>
        <div className="rounded-2xl bg-sand/60 p-5 sm:p-8">
          <ContactForm />
        </div>
      </section>
      <SiteFooter />
    </main>
  );
}
