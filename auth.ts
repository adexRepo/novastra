import NextAuth from 'next-auth';
import Credentials from 'next-auth/providers/credentials';
import { compare } from 'bcryptjs';
import { z } from 'zod';
import {
  getCustomerCredentialConfig,
  verifyCustomerCredentials,
} from '@/lib/auth/customer-credentials';

const credentialsSchema = z.object({
  username: z.string().min(3).max(80),
  password: z.string().min(8).max(200),
});

export const { handlers, auth, signIn, signOut } = NextAuth({
  trustHost: true,
  secret:
    process.env.AUTH_SECRET ??
    (process.env.NODE_ENV === 'development'
      ? 'novastra-local-development-secret-not-for-production'
      : undefined),
  session: { strategy: 'jwt' },
  pages: { signIn: '/login' },
  providers: [
    Credentials({
      id: 'customer-credentials',
      name: 'Pelanggan Novastra',
      credentials: {
        username: { label: 'Username', type: 'text' },
        password: { label: 'Password', type: 'password' },
      },
      async authorize(input) {
        const parsed = credentialsSchema.safeParse(input);
        if (!parsed.success) return null;

        const config = getCustomerCredentialConfig();
        if (
          !(await verifyCustomerCredentials(
            config,
            parsed.data.username,
            parsed.data.password,
          ))
        )
          return null;

        return {
          id: `customer:${config.email!}`,
          name: config.name!,
          email: config.email!,
          role: 'CUSTOMER',
        };
      },
    }),
    Credentials({
      id: 'admin-credentials',
      name: 'Admin Novastra',
      credentials: {
        username: { label: 'Username', type: 'text' },
        password: { label: 'Password', type: 'password' },
      },
      async authorize(input) {
        const parsed = credentialsSchema.safeParse(input);
        if (!parsed.success) return null;
        const username = process.env.ADMIN_USERNAME;
        const passwordHash = process.env.ADMIN_PASSWORD_HASH;
        const email = process.env.ADMIN_EMAIL;
        if (
          !username ||
          !passwordHash ||
          !email ||
          parsed.data.username !== username
        )
          return null;
        if (!(await compare(parsed.data.password, passwordHash))) return null;
        return {
          id: 'admin:environment',
          name: username,
          email,
          role: 'ADMIN',
        };
      },
    }),
  ],
  callbacks: {
    async jwt({ token, user }) {
      if (user)
        token.role =
          'role' in user && user.role === 'ADMIN' ? 'ADMIN' : 'CUSTOMER';
      return token;
    },
    async session({ session, token }) {
      if (session.user) {
        session.user.id = token.sub ?? '';
        session.user.role = token.role === 'ADMIN' ? 'ADMIN' : 'CUSTOMER';
      }
      return session;
    },
    authorized({ auth: session, request }) {
      const isAdminPath =
        request.nextUrl.pathname.startsWith('/admin') &&
        request.nextUrl.pathname !== '/admin/login';
      return !isAdminPath || session?.user?.role === 'ADMIN';
    },
  },
});
