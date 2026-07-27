<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HasImage
{
    /**
     * Lấy đường dẫn ảnh sản phẩm, trả về ảnh mặc định nếu không tồn tại
     *
     * @param string|null $image Đường dẫn ảnh
     * @return string
     */
    public function getImage($image = null)
    {
        if (!$image && isset($this->image)) {
            $image = $this->image;
        }

        return $this->resolveImageUrl($image) ?? asset('images/no-image.png');
    }

    /**
     * Resolve a single stored image path to a public URL, or null if missing.
     */
    protected function resolveImageUrl(?string $image): ?string
    {
        if (!$image) {
            return null;
        }

        $image = trim($image);
        if ($image === '') {
            return null;
        }

        $image = $this->normalizeStorageImagePath($image);

        if (str_starts_with($image, '/storage/')) {
            return $this->resolvePublicStorageUrl($image);
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        if (str_starts_with($image, '/')) {
            $publicPath = ltrim($image, '/');
            if (file_exists(public_path($publicPath))) {
                return asset($image);
            }
        }

        if (Storage::disk('public')->exists($image)) {
            return Storage::disk('public')->url($image);
        }

        $filename = basename($image);
        if ($filename && Storage::disk('public')->exists('images/' . $filename)) {
            return Storage::disk('public')->url('images/' . $filename);
        }

        return null;
    }

    /**
     * Convert absolute APP_URL/storage/... URLs back to /storage/... paths.
     */
    protected function normalizeStorageImagePath(string $image): string
    {
        $appUrl = rtrim((string) config('app.url', ''), '/');
        if ($appUrl !== '' && str_starts_with($image, $appUrl . '/storage/')) {
            return substr($image, strlen($appUrl));
        }

        return $image;
    }

    /**
     * Resolve /storage/... to a public URL. Prefer verified files, but always
     * return a URL for valid storage paths (matches admin textarea behaviour).
     */
    protected function resolvePublicStorageUrl(string $path): string
    {
        $storagePath = ltrim(str_replace('/storage/', '', $path), '/');

        if ($storagePath !== '' && Storage::disk('public')->exists($storagePath)) {
            return Storage::disk('public')->url($storagePath);
        }

        return asset($path);
    }

    /**
     * Resolve the first usable image URL from a list of stored paths.
     */
    protected function resolveImageUrlFromList(?array $images): ?string
    {
        if (empty($images) || !is_array($images)) {
            return null;
        }

        foreach ($images as $image) {
            $resolved = $this->resolveImageUrl($image);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return null;
    }

    /**
     * Check whether at least one path in the list resolves to a real file.
     */
    protected function imageUrlListHasResolvableFile(array $urls): bool
    {
        return $this->resolveImageUrlFromList($urls) !== null;
    }

    /**
     * Lấy đường dẫn ảnh đầu tiên hoặc ảnh mặc định
     *
     * @param string|array|null $imageUrls JSON string hoặc array chứa đường dẫn ảnh
     * @return string
     */
    public function getImageJson($imageUrls = null)
    {
        if (!$imageUrls && isset($this->image_urls)) {
            $imageUrls = $this->image_urls;
        }

        // Handle null or empty string
        if (empty($imageUrls)) {
            return asset('images/no-image.png');
        }

        $images = is_array($imageUrls) ? $imageUrls : json_decode($imageUrls, true);

        return $this->resolveImageUrlFromList($images) ?? asset('images/no-image.png');
    }


    /**
     * Lấy tất cả đường dẫn ảnh
     *
     * @param string|array|null $imageUrls JSON string hoặc array chứa đường dẫn ảnh
     * @return array
     */
    public function getAllImagesJson($imageUrls = null)
    {
        try {
            if (!$imageUrls && isset($this->image_urls)) {
                $imageUrls = $this->image_urls;
            }

            $images = is_array($imageUrls) ? $imageUrls : json_decode($imageUrls, true);

            if (!empty($images) && is_array($images)) {
                return array_map(function ($image) {
                    return $this->resolveImageUrl($image) ?? asset('images/no-image.png');
                }, $images);
            }
        } catch (\Exception $e) {
            \Log::error('Error in getAllImagesJson: ' . $e->getMessage());
        }

        return [asset('images/no-image.png')];
    }
}
