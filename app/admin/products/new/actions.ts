'use server';

import { redirect } from 'next/navigation';
import { z } from 'zod';
import { prisma } from '@/lib/db/prisma';
import { requireAdmin } from '@/lib/auth/guards';
import {
  deletePublicImage,
  saveProductImage,
} from '@/modules/file-storage/file-storage.service';

const schema = z.object({
  name: z.string().trim().min(2).max(120),
  slug: z.string().regex(/^[a-z0-9]+(?:-[a-z0-9]+)*$/),
  sku: z.string().trim().min(3).max(50),
  categoryId: z.string().min(1),
  shortDescription: z.string().trim().min(10).max(220),
  description: z.string().trim().min(20).max(5000),
  price: z.coerce.number().int().positive(),
  stock: z.coerce.number().int().min(0).max(1_000_000),
  featured: z.coerce.boolean().default(false),
});

export async function createProduct(
  _: { error?: string } | undefined,
  formData: FormData,
) {
  await requireAdmin();
  const parsed = schema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) return { error: 'Periksa kembali data produk.' };
  const file = formData.get('image');
  let imagePath: string | undefined;
  try {
    if (file instanceof File && file.size > 0)
      imagePath = (
        await saveProductImage(Buffer.from(await file.arrayBuffer()))
      ).imagePath;
    await prisma.product.create({
      data: {
        ...parsed.data,
        price: parsed.data.price,
        status: 'ACTIVE',
        imagePath,
      },
    });
  } catch (error) {
    if (imagePath) await deletePublicImage(imagePath).catch(() => undefined);
    if (error instanceof Error && error.message === 'FILE_TOO_LARGE')
      return { error: 'File terlalu besar. Maksimum 2 MB.' };
    if (error instanceof Error && error.message === 'UNSUPPORTED_FILE')
      return { error: 'Format file tidak didukung.' };
    return { error: 'Produk gagal disimpan.' };
  }
  redirect('/admin/products');
}
