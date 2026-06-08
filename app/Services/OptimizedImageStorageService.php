<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OptimizedImageStorageService
{
    public function storeUploadedFile(
        UploadedFile $file,
        string $disk,
        string $pathPrefix,
        ?string $suggestedFilenameWithExt = null
    ): string {
        if (! config('optimized_images.enabled', true)) {
            return $this->storeRawUploadedFile($file, $disk, $pathPrefix, $suggestedFilenameWithExt);
        }

        $pathPrefix = $this->normalizePathPrefix($pathPrefix);
        $targetBase = $this->resolveBaseName($file, $suggestedFilenameWithExt);
        $mime = $file->getMimeType() ?: '';
        $realPath = $file->getRealPath();
        if ($realPath === false) {
            return $this->storeRawUploadedFile($file, $disk, $pathPrefix, $suggestedFilenameWithExt);
        }

        if ($this->isSvg($file, $mime)) {
            $name = $pathPrefix . $targetBase . '.svg';

            return $this->putFile($disk, $name, file_get_contents($realPath) ?: '') ? $name : $this->storeRawUploadedFile($file, $disk, $pathPrefix, $suggestedFilenameWithExt);
        }

        if ($mime === 'image/gif' && $this->isAnimatedGif($realPath)) {
            $name = $pathPrefix . $targetBase . '.gif';

            return $this->putFile($disk, $name, file_get_contents($realPath) ?: '') ? $name : $this->storeRawUploadedFile($file, $disk, $pathPrefix, $suggestedFilenameWithExt);
        }

        $webpName = $pathPrefix . $targetBase . '.webp';
        if ($this->encodePathToWebpAndStore($realPath, $mime, $disk, $webpName)) {
            return $webpName;
        }

        return $this->storeRawUploadedFile($file, $disk, $pathPrefix, $suggestedFilenameWithExt);
    }

    /**
     * @param  string  $binary  Raw image bytes
     */
    public function storeBinaryAsWebp(
        string $binary,
        string $mime,
        string $disk,
        string $pathPrefix,
        string $suggestedFilenameWithExt
    ): string {
        $pathPrefix = $this->normalizePathPrefix($pathPrefix);
        $base = pathinfo($suggestedFilenameWithExt, PATHINFO_FILENAME) ?: Str::random(12);

        if (str_contains(strtolower($mime), 'svg')) {
            $name = $pathPrefix . $base . '.svg';
            $this->putFile($disk, $name, $binary);

            return $name;
        }

        if (! config('optimized_images.enabled', true)) {
            $ext = $this->extensionFromMime($mime) ?: 'bin';
            $name = $pathPrefix . $base . '.' . $ext;
            $this->putFile($disk, $name, $binary);

            return $name;
        }

        $webpName = $pathPrefix . $base . '.webp';

        if ($this->encodeBinaryToWebpAndStore($binary, $mime, $disk, $webpName)) {
            return $webpName;
        }

        $ext = $this->extensionFromMime($mime) ?: 'bin';
        $fallback = $pathPrefix . $base . '.' . $ext;
        $this->putFile($disk, $fallback, $binary);

        return $fallback;
    }

    private function storeRawUploadedFile(
        UploadedFile $file,
        string $disk,
        string $pathPrefix,
        ?string $suggestedFilenameWithExt
    ): string {
        $pathPrefix = $this->normalizePathPrefix($pathPrefix);
        $name = $suggestedFilenameWithExt
            ? $pathPrefix . basename($suggestedFilenameWithExt)
            : $pathPrefix . time() . '_' . Str::random(8) . '.' . ($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $contents = file_get_contents($file->getRealPath() ?: '');

        $this->putFile($disk, $name, $contents ?: '');

        return $name;
    }

    private function normalizePathPrefix(string $pathPrefix): string
    {
        $pathPrefix = trim($pathPrefix, '/');

        return $pathPrefix === '' ? '' : $pathPrefix . '/';
    }

    private function resolveBaseName(UploadedFile $file, ?string $suggestedFilenameWithExt): string
    {
        if ($suggestedFilenameWithExt) {
            $base = pathinfo($suggestedFilenameWithExt, PATHINFO_FILENAME);

            return $base !== '' ? Str::slug($base) : Str::random(12);
        }

        return Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: Str::random(12);
    }

    private function isSvg(UploadedFile $file, string $mime): bool
    {
        if (str_contains(strtolower($mime), 'svg')) {
            return true;
        }

        return str_ends_with(strtolower($file->getClientOriginalName()), '.svg');
    }

    private function isAnimatedGif(string $absolutePath): bool
    {
        $content = @file_get_contents($absolutePath, false, null, 0, 262_144);

        return is_string($content) && str_contains($content, 'NETSCAPE2.0');
    }

    private function encodePathToWebpAndStore(string $absolutePath, string $mime, string $disk, string $destPath): bool
    {
        if (! function_exists('imagewebp')) {
            return false;
        }

        $img = $this->createImageFromPath($absolutePath, $mime);
        if (! $img instanceof \GdImage) {
            return false;
        }

        try {
            $img = $this->resizeIfNeeded($img);
            $binary = $this->gdToWebpBinary($img);

            return $binary !== null && $this->putFile($disk, $destPath, $binary);
        } finally {
            imagedestroy($img);
        }
    }

    private function encodeBinaryToWebpAndStore(string $binary, string $mime, string $disk, string $destPath): bool
    {
        if (! function_exists('imagewebp')) {
            return false;
        }

        $img = @imagecreatefromstring($binary);
        if (! $img instanceof \GdImage) {
            return false;
        }

        try {
            $img = $this->resizeIfNeeded($img);
            $out = $this->gdToWebpBinary($img);

            return $out !== null && $this->putFile($disk, $destPath, $out);
        } finally {
            imagedestroy($img);
        }
    }

    /**
     * @return \GdImage|false
     */
    private function createImageFromPath(string $absolutePath, string $mime)
    {
        return match (true) {
            str_contains($mime, 'jpeg') || str_contains($mime, 'jpg') => @imagecreatefromjpeg($absolutePath),
            str_contains($mime, 'png') => @imagecreatefrompng($absolutePath),
            $mime === 'image/gif' => @imagecreatefromgif($absolutePath),
            str_contains($mime, 'webp') && function_exists('imagecreatefromwebp') => @imagecreatefromwebp($absolutePath),
            default => @imagecreatefromstring((string) file_get_contents($absolutePath)),
        };
    }

    private function resizeIfNeeded(\GdImage $img): \GdImage
    {
        $maxW = max(1, (int) config('optimized_images.max_width', 1920));
        $maxH = max(1, (int) config('optimized_images.max_height', 1920));
        $w = imagesx($img);
        $h = imagesy($img);
        if ($w <= 0 || $h <= 0) {
            return $img;
        }
        if ($w <= $maxW && $h <= $maxH) {
            return $this->prepareTrueColorWithAlpha($img);
        }

        $ratio = min($maxW / $w, $maxH / $h);
        $nw = max(1, (int) round($w * $ratio));
        $nh = max(1, (int) round($h * $ratio));
        $scaled = imagescale($img, $nw, $nh);
        if ($scaled instanceof \GdImage) {
            imagedestroy($img);

            return $this->prepareTrueColorWithAlpha($scaled);
        }

        return $this->prepareTrueColorWithAlpha($img);
    }

    private function prepareTrueColorWithAlpha(\GdImage $img): \GdImage
    {
        if (function_exists('imagepalettetotruecolor') && ! imageistruecolor($img)) {
            imagepalettetotruecolor($img);
        }
        imagealphablending($img, false);
        imagesavealpha($img, true);

        return $img;
    }

    private function gdToWebpBinary(\GdImage $img): ?string
    {
        $quality = max(0, min(100, (int) config('optimized_images.quality', 82)));
        ob_start();
        $ok = imagewebp($img, null, $quality);
        $binary = ob_get_clean();

        return $ok && is_string($binary) && $binary !== '' ? $binary : null;
    }

    private function putFile(string $disk, string $path, string $contents): bool
    {
        try {
            return Storage::disk($disk)->put($path, $contents);
        } catch (\Throwable) {
            return false;
        }
    }

    private function extensionFromMime(string $mime): ?string
    {
        $mime = strtolower($mime);

        return match (true) {
            str_contains($mime, 'jpeg') => 'jpg',
            str_contains($mime, 'png') => 'png',
            str_contains($mime, 'gif') => 'gif',
            str_contains($mime, 'webp') => 'webp',
            str_contains($mime, 'svg') => 'svg',
            default => null,
        };
    }
}
