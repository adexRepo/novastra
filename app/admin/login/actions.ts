'use server';

import { AuthError } from 'next-auth';
import { signIn } from '@/auth';
import { allowRequest } from '@/lib/rate-limit';

export async function loginAdmin(
  _: { error?: string } | undefined,
  formData: FormData,
) {
  const username = formData.get('username');
  if (
    typeof username !== 'string' ||
    !allowRequest(`admin-login:${username.toLowerCase()}`, 5, 15 * 60_000)
  )
    return { error: 'Terlalu banyak percobaan. Tunggu 15 menit.' };
  try {
    await signIn('admin-credentials', {
      username: formData.get('username'),
      password: formData.get('password'),
      redirectTo: '/admin',
    });
  } catch (error) {
    if (error instanceof AuthError)
      return { error: 'Username atau password tidak sesuai.' };
    throw error;
  }
}
