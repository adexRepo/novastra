'use client';

import { useActionState } from 'react';
import { LoaderCircle, Save } from 'lucide-react';
import { createProduct } from '@/app/admin/products/new/actions';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  NativeSelect,
  NativeSelectOption,
} from '@/components/ui/native-select';
import { Textarea } from '@/components/ui/textarea';

export function ProductForm({
  categories,
}: {
  categories: { id: string; name: string }[];
}) {
  const [state, action, pending] = useActionState(createProduct, undefined);
  return (
    <form
      action={action}
      className="mt-7 max-w-4xl rounded-xl border border-ink/10 bg-white p-5 sm:p-7"
    >
      <div className="grid gap-5 sm:grid-cols-2">
        <div className="sm:col-span-2">
          <Label htmlFor="name">Nama produk</Label>
          <Input
            id="name"
            name="name"
            required
            maxLength={120}
            className="mt-2"
          />
        </div>
        <div>
          <Label htmlFor="slug">Slug</Label>
          <Input
            id="slug"
            name="slug"
            required
            pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
            className="mt-2"
            placeholder="nama-produk"
          />
        </div>
        <div>
          <Label htmlFor="sku">SKU</Label>
          <Input id="sku" name="sku" required maxLength={50} className="mt-2" />
        </div>
        <div>
          <Label htmlFor="categoryId">Kategori</Label>
          <NativeSelect
            id="categoryId"
            name="categoryId"
            required
            className="mt-2 w-full"
          >
            <NativeSelectOption value="">Pilih kategori</NativeSelectOption>
            {categories.map((category) => (
              <NativeSelectOption key={category.id} value={category.id}>
                {category.name}
              </NativeSelectOption>
            ))}
          </NativeSelect>
        </div>
        <div className="grid grid-cols-2 gap-3">
          <div>
            <Label htmlFor="price">Harga (Rp)</Label>
            <Input
              id="price"
              name="price"
              type="number"
              min="1"
              required
              className="mt-2"
            />
          </div>
          <div>
            <Label htmlFor="stock">Stok</Label>
            <Input
              id="stock"
              name="stock"
              type="number"
              min="0"
              required
              className="mt-2"
            />
          </div>
        </div>
        <div className="sm:col-span-2">
          <Label htmlFor="shortDescription">Ringkasan</Label>
          <Input
            id="shortDescription"
            name="shortDescription"
            required
            minLength={10}
            maxLength={220}
            className="mt-2"
          />
        </div>
        <div className="sm:col-span-2">
          <Label htmlFor="description">Deskripsi</Label>
          <Textarea
            id="description"
            name="description"
            required
            minLength={20}
            maxLength={5000}
            className="mt-2 min-h-32"
          />
        </div>
        <div className="sm:col-span-2">
          <Label htmlFor="image">Gambar produk</Label>
          <Input
            id="image"
            name="image"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            className="mt-2 file:mr-3"
          />
          <p className="mt-1.5 text-xs text-muted-foreground">
            JPEG, PNG, atau WebP. Maksimum 2 MB.
          </p>
        </div>
        <div className="flex items-center gap-2">
          <Checkbox id="featured" name="featured" value="true" />
          <Label htmlFor="featured">Tampilkan sebagai produk unggulan</Label>
        </div>
      </div>
      {state?.error && (
        <p
          role="alert"
          className="mt-5 rounded-lg bg-red-50 p-3 text-sm text-red-700"
        >
          {state.error}
        </p>
      )}
      <div className="mt-7 flex justify-end">
        <Button type="submit" disabled={pending}>
          {pending ? (
            <>
              <LoaderCircle className="animate-spin" />
              Menyimpan...
            </>
          ) : (
            <>
              <Save />
              Simpan produk
            </>
          )}
        </Button>
      </div>
    </form>
  );
}
