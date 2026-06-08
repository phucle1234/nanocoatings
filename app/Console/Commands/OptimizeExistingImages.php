<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OptimizeExistingImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize
                            {--disk=public : Disk trong config/filesystems.php}
                            {--path=images : Đường dẫn tương đối gốc disk (vd: images)}
                            {--quality=80 : Chất lượng WebP 0–100}
                            {--dry-run : Chỉ liệt kê, không ghi file}';

    /**
     * @var array<int, string>
     */
    protected $aliases = ['images:optimize-existing'];

    protected $description = 'Nén và convert ảnh (JPEG/PNG/GIF) sang WebP trong storage (dùng GD, không cần Intervention)';

    public function handle(): int
    {
        if (! function_exists('imagewebp')) {
            $this->error('PHP GD không hỗ trợ WebP (thiếu imagewebp). Bật extension gd + webp trong php.ini.');

            return self::FAILURE;
        }

        $disk = (string) $this->option('disk');
        $relativePath = $this->normalizeRelativePath((string) $this->option('path'));
        $quality = max(0, min(100, (int) $this->option('quality')));
        $dryRun = (bool) $this->option('dry-run');

        if (! config("filesystems.disks.{$disk}")) {
            $this->error("Disk [{$disk}] không tồn tại trong config/filesystems.php.");

            return self::FAILURE;
        }

        $files = Storage::disk($disk)->allFiles($relativePath);
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

        $done = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($files as $path) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (! in_array($ext, $extensions, true)) {
                $skipped++;

                continue;
            }

            if ($ext === 'webp') {
                $skipped++;

                continue;
            }

            $fullPath = Storage::disk($disk)->path($path);
            if (! is_file($fullPath) || ! is_readable($fullPath)) {
                $this->warn("Bỏ qua (không đọc được): {$path}");
                $skipped++;

                continue;
            }

            $webpRelative = $this->webpPathFor($path);
            $webpFull = Storage::disk($disk)->path($webpRelative);

            if ($dryRun) {
                $this->line("[dry-run] {$path} → {$webpRelative}");
                $done++;

                continue;
            }

            if ($this->convertImageFileToWebp($fullPath, $webpFull, $quality)) {
                $this->info("OK: {$path} → {$webpRelative}");
                $done++;
            } else {
                $this->error("Lỗi: {$path}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Xong: {$done} xử lý, {$skipped} bỏ qua, {$failed} lỗi.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function normalizeRelativePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path), '/');

        // Cho phép người dùng dán nhầm đường dẫn đầy đủ kiểu storage/app/public/images
        $prefixes = [
            'storage/app/public/',
            'app/public/',
        ];
        foreach ($prefixes as $p) {
            if (str_starts_with($path, $p)) {
                $path = substr($path, strlen($p));

                break;
            }
        }

        return $path;
    }

    private function webpPathFor(string $relativePath): string
    {
        $dir = pathinfo($relativePath, PATHINFO_DIRNAME);
        $base = pathinfo($relativePath, PATHINFO_FILENAME);
        $suffix = ($dir !== '.' && $dir !== '') ? $dir . '/' . $base . '.webp' : $base . '.webp';

        return $suffix;
    }

    private function convertImageFileToWebp(string $sourceFullPath, string $destFullPath, int $quality): bool
    {
        $contents = @file_get_contents($sourceFullPath);
        if ($contents === false || $contents === '') {
            return false;
        }

        $image = @imagecreatefromstring($contents);
        if ($image === false) {
            return false;
        }

        $directory = dirname($destFullPath);
        if (! is_dir($directory)) {
            if (! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
                imagedestroy($image);

                return false;
            }
        }

        // PNG/GIF: giữ alpha
        if (function_exists('imagepalettetotruecolor')) {
            @imagepalettetotruecolor($image);
        }
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $ok = @imagewebp($image, $destFullPath, $quality);
        imagedestroy($image);

        return $ok === true;
    }
}
