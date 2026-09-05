'use client';

import { useActionState } from 'react';
import { Check, LoaderCircle, Send } from 'lucide-react';
import { submitContact } from '@/app/contact/actions';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

export function ContactForm() {
  const [state, action, pending] = useActionState(submitContact, undefined);
  if (state?.success)
    return (
      <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-6 text-emerald-800">
        <Check className="size-5" />
        <p className="mt-3 font-medium">Pesan sudah diterima.</p>
        <p className="mt-1 text-sm">
          Kami akan membalas secepatnya pada jam operasional.
        </p>
      </div>
    );
  return (
    <form action={action} className="space-y-5">
      <div>
        <Label htmlFor="contact-name">Nama</Label>
        <Input
          id="contact-name"
          name="name"
          required
          minLength={2}
          maxLength={100}
          className="mt-2 h-10 bg-white"
        />
      </div>
      <div>
        <Label htmlFor="contact-email">Email</Label>
        <Input
          id="contact-email"
          name="email"
          required
          type="email"
          maxLength={160}
          className="mt-2 h-10 bg-white"
        />
      </div>
      <div>
        <Label htmlFor="contact-message">Pesan</Label>
        <Textarea
          id="contact-message"
          name="message"
          required
          minLength={10}
          maxLength={1000}
          className="mt-2 min-h-32 bg-white"
        />
      </div>
      <div className="absolute -left-[9999px]" aria-hidden="true">
        <Label htmlFor="website">Website</Label>
        <Input id="website" name="website" tabIndex={-1} autoComplete="off" />
      </div>
      {state?.error && (
        <p
          role="alert"
          className="rounded-lg bg-red-50 p-3 text-sm text-red-700"
        >
          {state.error}
        </p>
      )}
      <Button type="submit" className="rounded-full" disabled={pending}>
        {pending ? (
          <>
            <LoaderCircle className="animate-spin" />
            Mengirim...
          </>
        ) : (
          <>
            <Send />
            Kirim pesan
          </>
        )}
      </Button>
    </form>
  );
}
