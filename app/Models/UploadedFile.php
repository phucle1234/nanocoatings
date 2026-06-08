<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UploadedFile extends Model
{
    protected $fillable = [
        'original_name',
        'stored_name',
        'path',
        'mime_type',
        'size',
        'sha256',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Quan hệ với User (người upload)
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Quan hệ với Posts
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'document_file_id');
    }

    /**
     * Quan hệ với Products
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'document_file_id');
    }

    /**
     * Lấy full path của file
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/' . $this->path . '/' . $this->stored_name);
    }

    /**
     * Kiểm tra file có tồn tại không
     */
    public function exists(): bool
    {
        return file_exists($this->full_path);
    }

    /**
     * Lấy file size dạng human readable
     */
    public function getHumanReadableSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
