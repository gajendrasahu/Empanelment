<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ViewFileService
{
    public function serve(string $filename, bool $inline = true)
    {
        $disk = Storage::disk('public');

        if (!$disk->exists($filename)) {
            return response()->view('errors.file_not_found', [], 404);
        }

        $path = $disk->path($filename);

        return response()->file($path, [
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . basename($path) . '"'
        ]);
    }
}