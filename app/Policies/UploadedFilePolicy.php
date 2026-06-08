<?php

namespace App\Policies;

use App\Models\UploadedFile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UploadedFilePolicy
{
    /**
     * Determine whether the user can view any models.
     * Admin có thể xem danh sách, user thường cũng có thể xem (để download)
     */
    public function viewAny(User $user): bool
    {
        return true; // Cả admin và user thường đều có thể xem danh sách
    }

    /**
     * Determine whether the user can view the model.
     * Admin và user thường đều có thể xem file để download
     */
    public function view(User $user, UploadedFile $uploadedFile): bool
    {
        return true; // Cả admin và user thường đều có thể xem/download file
    }

    /**
     * Determine whether the user can create models.
     * Chỉ admin mới được upload
     */
    public function create(User $user): bool
    {
        return $user->is_admin == 1 || $user->is_admin == '1';
    }

    /**
     * Determine whether the user can update the model.
     * Chỉ admin mới được update
     */
    public function update(User $user, UploadedFile $uploadedFile): bool
    {
        return $user->is_admin == 1 || $user->is_admin == '1';
    }

    /**
     * Determine whether the user can delete the model.
     * Chỉ admin mới được xóa
     */
    public function delete(User $user, UploadedFile $uploadedFile): bool
    {
        return $user->is_admin == 1 || $user->is_admin == '1';
    }

    /**
     * Determine whether the user can download the file.
     * Admin và user thường đều có thể download
     */
    public function download(User $user, UploadedFile $uploadedFile): bool
    {
        return true; // Cả admin và user thường đều có thể download
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UploadedFile $uploadedFile): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UploadedFile $uploadedFile): bool
    {
        return false;
    }
}
