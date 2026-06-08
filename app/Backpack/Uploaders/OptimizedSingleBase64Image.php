<?php

namespace App\Backpack\Uploaders;

use App\Services\OptimizedImageStorageService;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Uploaders\SingleBase64Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OptimizedSingleBase64Image extends SingleBase64Image
{
    public function uploadFiles(Model $entry, $value = null)
    {
        $value = $value ?? CRUD::getRequest()->get($this->getName());
        $previousImage = $this->getPreviousFiles($entry);

        if (! $value && $previousImage) {
            Storage::disk($this->getDisk())->delete($previousImage);

            return null;
        }

        if (Str::startsWith($value, 'data:image')) {
            if ($previousImage) {
                Storage::disk($this->getDisk())->delete($previousImage);
            }

            $mime = 'image/png';
            if (preg_match('#^data:([^;]+);base64,#', $value, $m)) {
                $mime = $m[1];
            }

            $base64Image = Str::after($value, ';base64,');
            $binary = base64_decode($base64Image, true);
            if ($binary === false) {
                return $previousImage;
            }

            $suggested = $this->getFileName($value);

            return app(OptimizedImageStorageService::class)->storeBinaryAsWebp(
                $binary,
                $mime,
                $this->getDisk(),
                $this->getPath(),
                $suggested
            );
        }

        return $previousImage;
    }

    public function uploadRepeatableFiles($values, $previousRepeatableValues, $entry = null)
    {
        $service = app(OptimizedImageStorageService::class);

        foreach ($values as $row => $rowValue) {
            if ($rowValue) {
                if (Str::startsWith($rowValue, 'data:image')) {
                    $mime = 'image/png';
                    if (preg_match('#^data:([^;]+);base64,#', $rowValue, $m)) {
                        $mime = $m[1];
                    }

                    $base64Image = Str::after($rowValue, ';base64,');
                    $binary = base64_decode($base64Image, true);
                    if ($binary === false) {
                        continue;
                    }

                    $suggested = $this->getFileName($rowValue);
                    $finalPath = $service->storeBinaryAsWebp(
                        $binary,
                        $mime,
                        $this->getDisk(),
                        $this->getPath(),
                        $suggested
                    );
                    $values[$row] = $previousRepeatableValues[] = $finalPath;

                    continue;
                }
            }
        }

        $imagesToDelete = array_diff($previousRepeatableValues, $values);

        foreach ($imagesToDelete as $image) {
            Storage::disk($this->getDisk())->delete($image);
        }

        return $values;
    }
}
