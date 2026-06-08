<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UploadedFile;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class FileUploadController extends Controller
{
    protected UploadService $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Upload file PDF
     * POST /admin/files/upload
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        // Check authorization (chỉ admin)
        // Sử dụng backpack_user() vì đây là admin route
        $user = backpack_user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để upload file'
            ], 401);
        }

        // Check if user is admin
        if (!($user->is_admin == 1 || $user->is_admin == '1')) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền upload file. Chỉ admin mới được phép upload.'
            ], 403);
        }

        // Validate request
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:30720', // 30MB = 30720 KB
            'context' => 'required|in:post,product',
        ], [
            'file.required' => 'Vui lòng chọn file',
            'file.file' => 'File không hợp lệ',
            'file.mimes' => 'Chỉ cho phép upload file PDF',
            'file.max' => 'File không được vượt quá 30MB',
            'context.required' => 'Context là bắt buộc',
            'context.in' => 'Context phải là "post" hoặc "product"',
        ]);

        try {
            $file = $request->file('file');
            $context = $request->input('context');
            $userId = backpack_user()->id;

            // Upload file
            $uploadedFile = $this->uploadService->uploadPdf($file, $context, $userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $uploadedFile->id,
                    'original_name' => $uploadedFile->original_name,
                ],
                'message' => 'Upload file thành công'
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);

        } catch (\Exception $e) {
            Log::error('File upload error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi upload file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download file
     * GET /admin/files/{id}/download hoặc /files/{id}/download
     * 
     * @param int $id
     * @return Response
     */
    public function download(int $id): Response
    {
        $uploadedFile = UploadedFile::findOrFail($id);

        // Check authorization (admin và user thường đều có thể download)
        // Support both backpack auth and regular auth
        $user = backpack_user() ?? auth()->user();
        if (!$user || !Gate::forUser($user)->allows('download', $uploadedFile)) {
            abort(403, 'Bạn không có quyền download file này');
        }

        // Check if file exists
        if (!$this->uploadService->fileExists($uploadedFile)) {
            abort(404, 'File không tồn tại');
        }

        // Get file content
        $fileContent = $this->uploadService->getFileContent($uploadedFile);
        if (!$fileContent) {
            abort(404, 'Không thể đọc file');
        }

        // Return file download response
        return response($fileContent, 200, [
            'Content-Type' => $uploadedFile->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $uploadedFile->original_name . '"',
            'Content-Length' => $uploadedFile->size,
        ]);
    }

    /**
     * View file (inline)
     * GET /admin/files/{id}/view hoặc /files/{id}/view
     * 
     * @param int $id
     * @return Response
     */
    public function view(int $id): Response
    {
        $uploadedFile = UploadedFile::findOrFail($id);

        // Check authorization
        // Support both backpack auth and regular auth
        $user = backpack_user() ?? auth()->user();
        if (!$user || !Gate::forUser($user)->allows('view', $uploadedFile)) {
            abort(403, 'Bạn không có quyền xem file này');
        }

        // Check if file exists
        if (!$this->uploadService->fileExists($uploadedFile)) {
            abort(404, 'File không tồn tại');
        }

        // Get file content
        $fileContent = $this->uploadService->getFileContent($uploadedFile);
        if (!$fileContent) {
            abort(404, 'Không thể đọc file');
        }

        // Return file inline response
        return response($fileContent, 200, [
            'Content-Type' => $uploadedFile->mime_type,
            'Content-Disposition' => 'inline; filename="' . $uploadedFile->original_name . '"',
            'Content-Length' => $uploadedFile->size,
        ]);
    }
}
