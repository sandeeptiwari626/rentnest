<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class PrivateUpload
{
    public static function store(UploadedFile $file, string $directory, string $errorField = 'file'): string
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                $errorField => $file->getErrorMessage()
                    ?: 'The file failed to upload. Try a file smaller than 8 MB.',
            ]);
        }

        $path = $file->store($directory, 'local');

        if (! is_string($path) || $path === '') {
            throw ValidationException::withMessages([
                $errorField => 'Could not save the file on the server. Please try again.',
            ]);
        }

        return $path;
    }
}
