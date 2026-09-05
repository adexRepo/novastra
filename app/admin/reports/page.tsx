import { Download } from 'lucide-react';
import Link from 'next/link';
import { AdminPageHeader } from '@/components/admin/page-header';
import { Button } from '@/components/ui/button';

export default function ReportsPage() {
  return (
    <>
      <AdminPageHeader
        eyebrow="Keuangan"
        title="Laporan"
        description="Ekspor hanya data pesanan yang diizinkan untuk admin."
      />
      <section className="mt-7 max-w-2xl rounded-xl border border-ink/10 bg-white p-6">
        <h2 className="font-display text-xl font-medium">Laporan pesanan</h2>
        <p className="mt-2 text-sm leading-6 text-muted-foreground">
          File Excel mencakup nomor pesanan, pelanggan, status, pembayaran, dan
          total. Data lain tidak ikut diekspor.
        </p>
        <Button
          render={<Link href="/api/admin/reports/orders" />}
          className="mt-6"
        >
          <Download />
          Unduh Excel
        </Button>
      </section>
    </>
  );
}
