'use server';

import { AuthError } from 'next-auth';
import { z } from 'zod';
import { signIn } from '@/auth';
import { safeCallbackUrl } from '@/lib/auth/callback-url';
import { allowRequest } from '@/lib/rate-limit';

const loginSchema = z.object({
  username: z.string().trim().min(3).max(80),
  password: z.string().min(8).max(200),
});

export type CustomerLoginState = { error?: string } | undefined;

export async function loginCustomer(
  _: CustomerLoginState,
  formData: FormData,
): Promise<CustomerLoginState> {
  const parsed = loginSchema.safeParse({
    username: formData.get('username'),
    password: formData.get('password'),
  });
  if (!parsed.success)
    return { error: 'Isi username dan password dengan benar.' };

  if (
    !allowRequest(
      `customer-login:${parsed.data.username.toLowerCase()}`,
      8,
      15 * 60_000,
    )
  )
    return { error: 'Terlalu banyak percobaan. Tunggu 15 menit.' };

  const redirectTo = safeCallbackUrl(formData.get('callbackUrl'));
  try {
    await signIn('customer-credentials', {
      username: parsed.data.username,
      password: parsed.data.password,
      redirectTo,
    });
  } catch (error) {
    if (error instanceof AuthError)
      return { error: 'Username atau password tidak sesuai.' };
    throw error;
  }
}
