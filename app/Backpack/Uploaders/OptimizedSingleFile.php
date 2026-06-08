<?php

namespace App\Backpack\Uploaders;

use App\Services\OptimizedImageStorageService;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade;
use Backpack\CRUD\app\Library\Uploaders\SingleFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OptimizedSingleFile extends SingleFile
{
    public function uploadFiles(Model $entry, $value = null)
    {
        $value = $value ?? CrudPanelFacade::getRequest()->file($this->getName());
        $previousFile = $this->getPreviousFiles($entry);

        if ($value === false && $previousFile) {
            Storage::disk($this->getDisk())->delete($previousFile);

            return null;
        }

        if ($value && is_file($value) && $value->isValid()) {
            if ($previousFile) {
                Storage::disk($this->getDisk())->delete($previousFile);
            }
            $suggested = $this->getFileName($value);

            return app(OptimizedImageStorageService::class)->storeUploadedFile(
                $value,
                $this->getDisk(),
                $this->getPath(),
                $suggested
            );
        }

        if (! $value && CrudPanelFacade::getRequest()->has($this->getNameForRequest()) && $previousFile) {
            Storage::disk($this->getDisk())->delete($previousFile);

            return null;
        }

        return $previousFile;
    }

    public function uploadRepeatableFiles($values, $previousRepeatableValues, $entry = null)
    {
        $orderedFiles = $this->getFileOrderFromRequest();
        $service = app(OptimizedImageStorageService::class);

        foreach ($values as $row => $file) {
            if ($file && is_file($file) && $file->isValid()) {
                $suggested = $this->getFileName($file);
                $orderedFiles[$row] = $service->storeUploadedFile(
                    $file,
                    $this->getDisk(),
                    $this->getPath(),
                    $suggested
                );

                continue;
            }
        }

        foreach ($previousRepeatableValues as $row => $file) {
            if ($file && ! isset($orderedFiles[$row])) {
                $orderedFiles[$row] = null;
                Storage::disk($this->getDisk())->delete($file);
            }
        }

        return $orderedFiles;
    }
}
