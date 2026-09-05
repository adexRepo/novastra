'use client';

import { useActionState } from 'react';
import { KeyRound, LoaderCircle } from 'lucide-react';
import { loginCustomer } from '@/app/login/actions';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type CustomerLoginFormProps = {
  callbackUrl: string;
  showDevelopmentCredentials?: boolean;
};

export function CustomerLoginForm({
  callbackUrl,
  showDevelopmentCredentials = false,
}: CustomerLoginFormProps) {
  const [state, action, pending] = useActionState(loginCustomer, undefined);

  return (
    <form action={action} className="mt-7 space-y-5">
      <input type="hidden" name="callbackUrl" value={callbackUrl} />
      <div>
        <Label htmlFor="customer-username">Username</Label>
        <Input
          id="customer-username"
          name="username"
          required
          minLength={3}
          maxLength={80}
          autoComplete="username"
          defaultValue={showDevelopmentCredentials ? 'pelanggan' : undefined}
          className="mt-2 h-11 bg-white"
        />
      </div>
      <div>
        <Label htmlFor="customer-password">Password</Label>
        <Input
          id="customer-password"
          name="password"
          type="password"
          required
          minLength={8}
          maxLength={200}
          autoComplete="current-password"
          className="mt-2 h-11 bg-white"
        />
      </div>

      {showDevelopmentCredentials && (
        <p className="rounded-xl border border-brand/15 bg-brand/5 p-3 text-xs leading-5 text-ink/75">
          Akun development: <strong>pelanggan</strong> /{' '}
          <strong>novastra123</strong>
        </p>
      )}

      {state?.error && (
        <p
          role="alert"
          className="rounded-xl bg-red-50 p-3 text-sm text-red-700"
        >
          {state.error}
        </p>
      )}

      <Button
        type="submit"
        size="lg"
        className="h-12 w-full rounded-full"
        disabled={pending}
      >
        {pending ? (
          <>
            <LoaderCircle className="animate-spin" />
            Memeriksa akun...
          </>
        ) : (
          <>
            <KeyRound />
            Masuk sebagai pelanggan
          </>
        )}
      </Button>
    </form>
  );
}
