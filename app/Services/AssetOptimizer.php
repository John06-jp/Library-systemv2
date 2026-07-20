<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use InvalidArgumentException;

class AssetOptimizer
{
    /**
     * Optimize an uploaded image and return its path on the public disk.
     */
    public function optimize(UploadedFile $file, string $type, string $directory): string
    {
        $dimensions = match ($type) {
            'banner' => config('branding.optimization.max_dimensions.banner', [4000, 2000]),
            'logo', 'book_cover' => config('branding.optimization.max_dimensions.logo', [1000, 1000]),
            default => throw new InvalidArgumentException("Unknown branding asset type [{$type}]."),
        };

        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;

        if (! in_array($extension, ['jpg', 'png', 'webp'], true)) {
            throw new InvalidArgumentException('Branding assets must be JPG, PNG, or WebP images.');
        }

        $image = Image::make($file->getRealPath())->orientate();
        $image->resize($dimensions[0], $dimensions[1], function ($constraint): void {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $quality = match ($extension) {
            'jpg' => (int) config('branding.optimization.jpeg_quality', 85),
            'webp' => (int) config('branding.optimization.webp_quality', 80),
            'png' => (int) config('branding.optimization.png_compression', 7),
        };
        $encoded = $image->encode($extension, $quality);

        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }
}
