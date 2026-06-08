<?php

namespace App\Backpack\Uploaders;

use App\Services\OptimizedImageStorageService;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Uploaders\MultipleFiles;
use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\UploaderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class OptimizedMultipleFiles extends MultipleFiles
{
    public static function for(array $field, $configuration): UploaderInterface
    {
        return (new self($field, $configuration))->multiple();
    }

    public function uploadFiles(Model $entry, $value = null)
    {
        if ($value && isset($value[0]) && is_null($value[0])) {
            $value = false;
        }

        $filesToDelete = $this->getFilesToDeleteFromRequest();
        $value = $value ?? collect(CRUD::getRequest()->file($this->getNameForRequest()))->flatten()->toArray();
        $previousFiles = $this->getPreviousFiles($entry) ?? [];

        if (is_array($previousFiles) && empty($previousFiles[0] ?? [])) {
            $previousFiles = [];
        }

        if (! is_array($previousFiles) && is_string($previousFiles)) {
            $previousFiles = json_decode($previousFiles, true);
        }

        if ($filesToDelete) {
            foreach ($previousFiles as $previousFile) {
                if (in_array($previousFile, $filesToDelete)) {
                    Storage::disk($this->getDisk())->delete($previousFile);

                    $previousFiles = Arr::where($previousFiles, function ($value, $key) use ($previousFile) {
                        return $value != $previousFile;
                    });
                }
            }
        }

        if (! is_array($value)) {
            $value = [];
        }

        $service = app(OptimizedImageStorageService::class);

        foreach ($value as $file) {
            if ($file && is_file($file)) {
                $suggested = $this->getFileName($file);
                $previousFiles[] = $service->storeUploadedFile(
                    $file,
                    $this->getDisk(),
                    $this->getPath(),
                    $suggested
                );
            }
        }

        return isset($entry->getCasts()[$this->getName()]) ? $previousFiles : json_encode($previousFiles);
    }

    public function uploadRepeatableFiles($files, $previousRepeatableValues, $entry = null)
    {
        $fileOrder = $this->getFileOrderFromRequest();
        $service = app(OptimizedImageStorageService::class);

        foreach ($files as $row => $files) {
            foreach ($files ?? [] as $file) {
                if ($file && is_file($file)) {
                    $suggested = $this->getFileName($file);
                    $fileOrder[$row][] = $service->storeUploadedFile(
                        $file,
                        $this->getDisk(),
                        $this->getPath(),
                        $suggested
                    );
                }
            }
        }

        foreach ($previousRepeatableValues as $previousRow => $previousFiles) {
            foreach ($previousFiles ?? [] as $key => $file) {
                $key = array_search($file, $fileOrder, true);
                if ($key === false) {
                    Storage::disk($this->getDisk())->delete($file);
                }
            }
        }

        return $fileOrder;
    }

    /**
     * @return array<int, string>
     */
    private function getFilesToDeleteFromRequest(): array
    {
        return collect(CRUD::getRequest()->get('clear_'.$this->getNameForRequest()))->flatten()->toArray();
    }
}
