'use server';

import { z } from 'zod';
import { auth } from '@/auth';
import { prisma } from '@/lib/db/prisma';
import { createOrder } from '@/modules/order/order.service';
import { notifyOrderCreated } from '@/modules/notification/notification.service';
import { allowRequest } from '@/lib/rate-limit';

const checkoutSchema = z.object({
  name: z.string().trim().min(2).max(100),
  email: z.email().max(160),
  phone: z.string().trim().min(8).max(20),
  address: z.string().trim().min(10).max(500),
  notes: z.string().trim().max(300).optional(),
  cart: z.string().transform((value, context) => {
    try {
      return z
        .array(
          z.object({
            productId: z.string().min(1),
            quantity: z.number().int().min(1).max(100),
          }),
        )
        .min(1)
        .max(50)
        .parse(JSON.parse(value));
    } catch {
      context.addIssue({ code: 'custom', message: 'Keranjang tidak valid.' });
      return z.NEVER;
    }
  }),
});

export type CheckoutState =
  | { error?: string; orderId?: string; orderNumber?: string }
  | undefined;

export async function submitCheckout(
  _: CheckoutState,
  formData: FormData,
): Promise<CheckoutState> {
  const session = await auth();
  if (!session?.user?.email)
    return {
      error: 'Silakan masuk sebagai pelanggan sebelum membuat pesanan.',
    };
  const parsed = checkoutSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success)
    return { error: 'Periksa kembali data pengiriman dan keranjang Anda.' };
  if (!allowRequest(`checkout:${session.user.email.toLowerCase()}`, 5, 60_000))
    return {
      error: 'Terlalu banyak percobaan. Tunggu sebentar lalu coba lagi.',
    };
  if (parsed.data.email.toLowerCase() !== session.user.email.toLowerCase())
    return { error: 'Email pesanan harus sama dengan akun yang masuk.' };
  try {
    const customer = await prisma.customer.upsert({
      where: { email: session.user.email },
      update: {
        name: session.user.name ?? parsed.data.name,
        avatar: session.user.image,
      },
      create: {
        email: session.user.email,
        name: session.user.name ?? parsed.data.name,
        avatar: session.user.image,
        provider: 'credentials',
      },
    });
    const order = await createOrder({
      customerId: customer.id,
      customerName: parsed.data.name,
      customerEmail: session.user.email,
      phone: parsed.data.phone,
      address: parsed.data.address,
      notes: parsed.data.notes || undefined,
      items: parsed.data.cart,
    });
    await notifyOrderCreated(order);
    return { orderId: order.id, orderNumber: order.orderNumber };
  } catch (error) {
    if (error instanceof Error && error.message === 'INSUFFICIENT_STOCK')
      return { error: 'Stok berubah. Periksa kembali jumlah di keranjang.' };
    if (error instanceof Error && error.message === 'PRODUCT_UNAVAILABLE')
      return { error: 'Salah satu produk sudah tidak tersedia.' };
    return { error: 'Pesanan belum berhasil dibuat. Silakan coba lagi.' };
  }
}
