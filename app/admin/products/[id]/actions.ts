'use server';

import { revalidatePath } from 'next/cache';
import { requireAdmin } from '@/lib/auth/guards';
import { prisma } from '@/lib/db/prisma';

export async function toggleProductStatus(formData: FormData) {
  await requireAdmin();
  const value = formData.get('id');
  const id = typeof value === 'string' ? value : '';
  const product = await prisma.product.findUnique({
    where: { id },
    select: { status: true },
  });
  if (!product) throw new Error('NOT_FOUND');
  await prisma.product.update({
    where: { id },
    data: { status: product.status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE' },
  });
  revalidatePath(`/admin/products/${id}`);
  revalidatePath('/admin/products');
  revalidatePath('/products');
}
