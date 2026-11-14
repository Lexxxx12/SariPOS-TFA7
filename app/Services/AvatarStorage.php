<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\ImageManager;

class AvatarStorage
{
    public function store(UploadedFile $file): string
    {
        try {
            $image = (new ImageManager(new Driver))->read($file->getPathname())->cover(256, 256)->toJpeg(85);
        } catch (DecoderException $exception) {
            throw ValidationException::withMessages(['avatar' => 'This image could not be read. Please choose another JPG, PNG, or WebP file.']);
        }
        $path = 'avatars/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, (string) $image);

        return $path;
    }
}
