'use client';

import { useActionState } from 'react';
import { LoaderCircle, LockKeyhole } from 'lucide-react';
import { loginAdmin } from '@/app/admin/login/actions';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export function AdminLoginForm() {
  const [state, action, pending] = useActionState(loginAdmin, undefined);
  return (
    <form action={action} className="mt-8 space-y-5">
      <div>
        <Label htmlFor="username">Username</Label>
        <Input
          id="username"
          name="username"
          required
          autoComplete="username"
          className="mt-2 h-10 bg-white"
        />
      </div>
      <div>
        <Label htmlFor="password">Password</Label>
        <Input
          id="password"
          name="password"
          type="password"
          required
          minLength={8}
          autoComplete="current-password"
          className="mt-2 h-10 bg-white"
        />
      </div>
      {state?.error && (
        <p
          role="alert"
          className="rounded-lg bg-red-50 p-3 text-sm text-red-700"
        >
          {state.error}
        </p>
      )}
      <Button
        type="submit"
        size="lg"
        className="h-11 w-full rounded-full"
        disabled={pending}
      >
        {pending ? (
          <>
            <LoaderCircle className="animate-spin" />
            Memeriksa...
          </>
        ) : (
          <>
            <LockKeyhole />
            Masuk ke admin
          </>
        )}
      </Button>
    </form>
  );
}
