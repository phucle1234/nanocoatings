<?php

namespace App\Services;

use App\Models\UploadedFile;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UploadService
{
    /**
     * Upload file PDF và lưu metadata
     * 
     * @param HttpUploadedFile $file
     * @param string $context 'post' hoặc 'product'
     * @param int|null $userId ID của user upload (admin)
     * @return UploadedFile
     * @throws \Exception
     */
    public function uploadPdf(HttpUploadedFile $file, string $context, ?int $userId = null): UploadedFile
    {
        // Validate context
        if (!in_array($context, ['post', 'product'])) {
            throw new \InvalidArgumentException("Context phải là 'post' hoặc 'product'");
        }

        // Validate file type
        if ($file->getMimeType() !== 'application/pdf') {
            throw new \InvalidArgumentException('Chỉ cho phép upload file PDF');
        }

        // Validate file size (30MB = 31457280 bytes)
        $maxSize = 30 * 1024 * 1024; // 30MB
        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('File không được vượt quá 30MB');
        }

        // Generate UUID cho tên file
        $uuid = Str::uuid()->toString();
        $storedName = $uuid . '.pdf';

        // Determine storage path
        $path = 'uploads/documents/' . $context . 's'; // posts hoặc products

        // Store file on local disk
        $storedPath = $file->storeAs($path, $storedName, 'local');

        if (!$storedPath) {
            throw new \Exception('Không thể lưu file');
        }

        // Calculate SHA256 hash
        $fileContent = Storage::disk('local')->get($storedPath);
        $sha256 = hash('sha256', $fileContent);

        // Check if file with same hash already exists
        $existingFile = UploadedFile::where('sha256', $sha256)->first();
        if ($existingFile) {
            $existingFilePath = $existingFile->path . '/' . $existingFile->stored_name;
            if (Storage::disk('local')->exists($existingFilePath)) {
                // Delete the duplicate we just uploaded
                Storage::disk('local')->delete($storedPath);
                
                // Return existing file
                return $existingFile;
            }
        }

        // Create metadata record
        $uploadedFile = UploadedFile::create([
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'sha256' => $sha256,
            'uploaded_by' => $userId,
        ]);

        Log::info('File uploaded successfully', [
            'file_id' => $uploadedFile->id,
            'original_name' => $uploadedFile->original_name,
            'stored_name' => $uploadedFile->stored_name,
            'context' => $context,
            'user_id' => $userId,
        ]);

        return $uploadedFile;
    }

    /**
     * Xóa file và metadata
     * 
     * @param UploadedFile $uploadedFile
     * @return bool
     */
    public function deleteFile(UploadedFile $uploadedFile): bool
    {
        // Check if file is still in use
        if ($uploadedFile->posts()->exists() || $uploadedFile->products()->exists()) {
            throw new \Exception('File đang được sử dụng, không thể xóa');
        }

        // Delete physical file
        $filePath = $uploadedFile->path . '/' . $uploadedFile->stored_name;
        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        }

        // Delete metadata
        return $uploadedFile->delete();
    }

    /**
     * Lấy file content để download
     * 
     * @param UploadedFile $uploadedFile
     * @return string|null
     */
    public function getFileContent(UploadedFile $uploadedFile): ?string
    {
        $filePath = $uploadedFile->path . '/' . $uploadedFile->stored_name;
        
        if (!Storage::disk('local')->exists($filePath)) {
            return null;
        }

        return Storage::disk('local')->get($filePath);
    }

    /**
     * Kiểm tra file có tồn tại không
     * 
     * @param UploadedFile $uploadedFile
     * @return bool
     */
    public function fileExists(UploadedFile $uploadedFile): bool
    {
        $filePath = $uploadedFile->path . '/' . $uploadedFile->stored_name;
        return Storage::disk('local')->exists($filePath);
    }
}

