import { join, resolve } from 'node:path';

export const storageConfig = {
  publicDir: process.env.PUBLIC_UPLOAD_DIR
    ? resolve(/* turbopackIgnore: true */ process.env.PUBLIC_UPLOAD_DIR)
    : join(process.cwd(), 'public', 'uploads'),
  privateDir: process.env.PRIVATE_UPLOAD_DIR
    ? resolve(/* turbopackIgnore: true */ process.env.PRIVATE_UPLOAD_DIR)
    : join(process.cwd(), 'storage', 'private'),
  tempDir: process.env.TEMP_UPLOAD_DIR
    ? resolve(/* turbopackIgnore: true */ process.env.TEMP_UPLOAD_DIR)
    : join(process.cwd(), 'storage', 'tmp'),
  maxProductImageBytes: 2 * 1024 * 1024,
};
