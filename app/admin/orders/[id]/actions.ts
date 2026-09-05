'use server';

import { revalidatePath } from 'next/cache';
import { z } from 'zod';
import { requireAdmin } from '@/lib/auth/guards';
import {
  transitionOrder,
  transitionPayment,
} from '@/modules/order/order.service';

const orderTarget = z.enum([
  'CONFIRMED',
  'PROCESSING',
  'SHIPPED',
  'COMPLETED',
  'CANCELLED',
]);
const paymentTarget = z.enum(['PENDING', 'PAID', 'FAILED', 'REFUNDED']);
export async function updateOrderStatus(formData: FormData) {
  await requireAdmin();
  const value = formData.get('id');
  const id = typeof value === 'string' ? value : '';
  await transitionOrder(id, orderTarget.parse(formData.get('target')));
  revalidatePath(`/admin/orders/${id}`);
}
export async function updatePaymentStatus(formData: FormData) {
  await requireAdmin();
  const value = formData.get('id');
  const id = typeof value === 'string' ? value : '';
  await transitionPayment(id, paymentTarget.parse(formData.get('target')));
  revalidatePath(`/admin/orders/${id}`);
}
