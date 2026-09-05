import { AdminShell } from '@/components/admin/admin-shell';
import { auth } from '@/auth';

export const dynamic = 'force-dynamic';
export default async function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const session = await auth();
  return (
    <AdminShell adminName={session?.user?.name ?? 'Admin'}>
      {children}
    </AdminShell>
  );
}
