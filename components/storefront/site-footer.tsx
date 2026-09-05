import Link from 'next/link';

export function SiteFooter() {
  return (
    <footer id="contact" className="bg-[#0f2f20] py-12 text-paper">
      <div className="page-shell grid gap-10 border-b border-white/10 pb-10 sm:grid-cols-2 lg:grid-cols-4">
        <div>
          <p className="font-display text-xl font-semibold">novastra</p>
          <p className="mt-3 max-w-xs text-sm leading-6 text-paper/55">
            Bahan segar untuk masak sehari-hari.
          </p>
        </div>
        <div>
          <p className="footer-title">Belanja</p>
          <div className="footer-links">
            <Link href="/products">Semua produk</Link>
            <Link href="/categories">Kategori</Link>
            <Link href="/orders">Pesanan saya</Link>
          </div>
        </div>
        <div>
          <p className="footer-title">Perusahaan</p>
          <div className="footer-links">
            <Link href="/about">Tentang kami</Link>
            <Link href="/contact">Kontak</Link>
          </div>
        </div>
        <div>
          <p className="footer-title">Hubungi</p>
          <div className="footer-links">
            <a href="mailto:halo@novastra.id">halo@novastra.id</a>
            <a href="https://wa.me/6281234567890">WhatsApp</a>
            <span>Senin–Sabtu, 09.00–17.00</span>
          </div>
        </div>
      </div>
      <div className="page-shell flex flex-col gap-2 pt-6 text-xs text-paper/40 sm:flex-row sm:justify-between">
        <span>© 2026 Novastra. Hak cipta dilindungi.</span>
        <span>Pengiriman dari Indonesia.</span>
      </div>
    </footer>
  );
}
