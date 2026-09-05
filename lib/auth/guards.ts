import { redirect } from 'next/navigation';
import { auth } from '@/auth';

export async function requireAdmin() {
  const session = await auth();
  if (!session?.user || session.user.role !== 'ADMIN') redirect('/admin/login');
  return session.user;
}

export async function requireCustomer() {
  const session = await auth();
  if (!session?.user || session.user.role !== 'CUSTOMER')
    redirect('/login?callbackUrl=%2Fcheckout');
  return session.user;
}

export async function requireOrderOwner(customerId: string) {
  const user = await requireCustomer();
  if (user.id !== customerId) throw new Error('FORBIDDEN');
  return user;
}
