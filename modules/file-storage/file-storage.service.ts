import { randomUUID } from 'node:crypto';
import { mkdir, readFile, rm, writeFile } from 'node:fs/promises';
import { extname, join, relative, resolve, sep } from 'node:path';
import { fileTypeFromBuffer } from 'file-type';
import sharp from 'sharp';
import { storageConfig } from './storage.config';

const allowed = new Set(['image/jpeg', 'image/png', 'image/webp']);

function inside(base: string, target: string) {
  const value = relative(resolve(base), resolve(target));
  return (
    value !== '' &&
    !value.startsWith(`..${sep}`) &&
    value !== '..' &&
    !resolve(value).includes(`${sep}..${sep}`)
  );
}

export async function saveProductImage(buffer: Buffer) {
  if (
    buffer.byteLength === 0 ||
    buffer.byteLength > storageConfig.maxProductImageBytes
  )
    throw new Error('FILE_TOO_LARGE');
  const type = await fileTypeFromBuffer(buffer);
  if (!type || !allowed.has(type.mime)) throw new Error('UNSUPPORTED_FILE');
  const metadata = await sharp(buffer, { failOn: 'error' }).metadata();
  if (
    !metadata.width ||
    !metadata.height ||
    metadata.width > 6000 ||
    metadata.height > 6000
  )
    throw new Error('INVALID_DIMENSIONS');
  const directory = join(storageConfig.publicDir, 'products');
  await mkdir(directory, { recursive: true });
  const filename = `${randomUUID()}.webp`;
  const target = join(directory, filename);
  if (!inside(storageConfig.publicDir, target))
    throw new Error('INVALID_STORAGE_PATH');
  const optimized = await sharp(buffer)
    .rotate()
    .resize({
      width: 1600,
      height: 1600,
      fit: 'inside',
      withoutEnlargement: true,
    })
    .webp({ quality: 82 })
    .toBuffer();
  await writeFile(target, optimized, { flag: 'wx' });
  return {
    imagePath: `/uploads/products/${filename}`,
    size: optimized.byteLength,
    mime: 'image/webp' as const,
  };
}

export async function deletePublicImage(imagePath: string) {
  if (!imagePath.startsWith('/uploads/'))
    throw new Error('INVALID_STORAGE_PATH');
  const target = join(
    storageConfig.publicDir,
    imagePath.slice('/uploads/'.length),
  );
  if (!inside(storageConfig.publicDir, target) || extname(target) !== '.webp')
    throw new Error('INVALID_STORAGE_PATH');
  await rm(target, { force: true });
}

export async function getPrivateFile(relativePath: string) {
  const target = join(storageConfig.privateDir, relativePath);
  if (!inside(storageConfig.privateDir, target))
    throw new Error('INVALID_STORAGE_PATH');
  return readFile(target);
}
