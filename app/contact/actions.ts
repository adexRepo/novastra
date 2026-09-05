'use server';

import { z } from 'zod';
import { prisma } from '@/lib/db/prisma';
import { sendMail } from '@/lib/email/mailer';
import { allowRequest } from '@/lib/rate-limit';

const schema = z.object({
  name: z.string().trim().min(2).max(100),
  email: z.email().max(160),
  message: z.string().trim().min(10).max(1000),
  website: z.string().max(0),
});
export type ContactState = { success?: boolean; error?: string } | undefined;

export async function submitContact(
  _: ContactState,
  formData: FormData,
): Promise<ContactState> {
  const parsed = schema.safeParse(Object.fromEntries(formData));
  if (!parsed.success)
    return { error: 'Periksa kembali nama, email, dan pesan Anda.' };
  if (
    !allowRequest(`contact:${parsed.data.email.toLowerCase()}`, 3, 60 * 60_000)
  )
    return { error: 'Terlalu banyak pesan. Silakan coba lagi nanti.' };
  try {
    await prisma.feedback.create({
      data: {
        name: parsed.data.name,
        email: parsed.data.email,
        message: parsed.data.message,
      },
    });
    const adminEmail = process.env.ADMIN_EMAIL;
    if (adminEmail)
      await sendMail({
        to: adminEmail,
        subject: `Pesan dari ${parsed.data.name}`,
        text: `${parsed.data.message}\n\nBalas ke: ${parsed.data.email}`,
      }).catch((error) =>
        console.error(
          'Contact notification failed',
          error instanceof Error ? error.message : 'UNKNOWN',
        ),
      );
    return { success: true };
  } catch {
    return {
      error:
        'Pesan belum berhasil dikirim. Silakan hubungi kami melalui WhatsApp.',
    };
  }
}
