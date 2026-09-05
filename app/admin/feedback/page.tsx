import { AdminPageHeader } from '@/components/admin/page-header';
import { prisma } from '@/lib/db/prisma';

export default async function FeedbackPage() {
  const feedback = await prisma.feedback.findMany({
    orderBy: { createdAt: 'desc' },
    take: 100,
  });
  return (
    <>
      <AdminPageHeader
        eyebrow="Pelanggan"
        title="Feedback"
        description="Pesan terbaru dari pelanggan dan halaman kontak."
      />
      <div className="mt-7 grid gap-3 xl:grid-cols-2">
        {feedback.map((item) => (
          <article
            key={item.id}
            className="rounded-xl border border-ink/10 bg-white p-5"
          >
            <div className="flex justify-between gap-4">
              <div>
                <h2 className="font-medium">{item.name}</h2>
                <p className="text-xs text-muted-foreground">{item.email}</p>
              </div>
              <time className="text-xs text-muted-foreground">
                {new Intl.DateTimeFormat('id-ID', {
                  dateStyle: 'medium',
                }).format(item.createdAt)}
              </time>
            </div>
            <p className="mt-4 text-sm leading-6 text-muted-foreground">
              {item.message}
            </p>
          </article>
        ))}
        {feedback.length === 0 && (
          <p className="text-sm text-muted-foreground">Belum ada feedback.</p>
        )}
      </div>
    </>
  );
}
