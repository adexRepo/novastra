<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class FeedbackEvidenceStorage
{
    public function save(UploadedFile $file): string
    {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = $file->getMimeType();

        if (! isset($allowed[$mime]) || $file->getSize() > 2 * 1024 * 1024 || ! @getimagesize($file->getRealPath())) {
            throw new RuntimeException('INVALID_FEEDBACK_EVIDENCE');
        }

        $directory = $this->root().DIRECTORY_SEPARATOR.'feedback';
        File::ensureDirectoryExists($directory, 0700, true);
        if (DIRECTORY_SEPARATOR === '/' && ! @chmod($directory, 0700)) {
            throw new RuntimeException('PRIVATE_DIRECTORY_PERMISSION_FAILED');
        }

        $filename = Str::uuid().'.'.$allowed[$mime];
        $file->move($directory, $filename);
        $path = $directory.DIRECTORY_SEPARATOR.$filename;

        if (DIRECTORY_SEPARATOR === '/' && ! @chmod($path, 0600)) {
            File::delete($path);
            throw new RuntimeException('PRIVATE_FILE_PERMISSION_FAILED');
        }

        return 'feedback/'.$filename;
    }

    public function path(string $relativePath): string
    {
        if (! preg_match('#^feedback/[a-f0-9-]+\.(jpg|png|webp)$#i', $relativePath)) {
            throw new RuntimeException('INVALID_FEEDBACK_EVIDENCE_PATH');
        }

        return $this->root().DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    public function delete(?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        try {
            File::delete($this->path($relativePath));
        } catch (RuntimeException) {
            // Ignore legacy or malformed paths instead of deleting outside the private directory.
        }
    }

    private function root(): string
    {
        return rtrim((string) config('novastra.private_upload_dir'), DIRECTORY_SEPARATOR);
    }
}
