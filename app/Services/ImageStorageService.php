<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class ImageStorageService
{
    public function save(UploadedFile $file, string $folder = 'products'): string
    {
        if (! in_array($folder, ['products', 'categories'], true)) {
            throw new RuntimeException('INVALID_UPLOAD_FOLDER');
        }

        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = $file->getMimeType();
        if (! isset($allowed[$mime]) || $file->getSize() > 2 * 1024 * 1024 || ! @getimagesize($file->getRealPath())) {
            throw new RuntimeException('INVALID_IMAGE');
        }
        $name = (string) Str::uuid().'.'.$allowed[$mime];
        $root = rtrim((string) config('novastra.public_upload_dir'), DIRECTORY_SEPARATOR);
        $directory = $root.DIRECTORY_SEPARATOR.$folder;
        File::ensureDirectoryExists($directory, 0755, true);
        $file->move($directory, $name);

        return '/uploads/'.$folder.'/'.$name;
    }

    public function delete(?string $relativePath): void
    {
        if (! $relativePath || ! preg_match('#^/uploads/(products|categories)/[a-f0-9-]+\.(jpg|png|webp)$#i', $relativePath)) {
            return;
        }
        $root = rtrim((string) config('novastra.public_upload_dir'), DIRECTORY_SEPARATOR);
        $path = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, ltrim(substr($relativePath, strlen('/uploads/')), '/'));
        File::delete($path);
    }
}
