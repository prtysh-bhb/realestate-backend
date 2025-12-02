<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ContentService
{
    /*
    |--------------------------------------------------------------------------
    | Generic CRUD Operations for FAQs, Blogs, News
    |--------------------------------------------------------------------------
    */

    public function list($model)
    {
        return $model::latest()->get();
    }

    public function create($model, $data, $imageField = null, $folder = null)
    {
        if ($imageField && isset($data[$imageField])) {
            $data[$imageField] = $this->uploadImage($data[$imageField], $folder);
        }

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        return $model::create($data);
    }

    public function update($model, $id, $data, $imageField = null, $folder = null)
    {
        $item = $model::findOrFail($id);

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if ($imageField && isset($data[$imageField])) {
            $this->deleteImage($item->$imageField, $folder);
            $data[$imageField] = $this->uploadImage($data[$imageField], $folder);
        }

        $item->update($data);

        return $item;
    }

    public function delete($model, $id, $imageField = null, $folder = null)
    {
        $item = $model::findOrFail($id);

        if ($imageField && $item->$imageField) {
            $this->deleteImage($item->$imageField, $folder);
        }

        $item->delete();

        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Image Upload Helpers (Blogs + News)
    |--------------------------------------------------------------------------
    */

    public function uploadImage($image, $folder)
    {
        // Generate filename
        $filename = time() . '-' . Str::random(10) . '.' . $image->getClientOriginalExtension();

        // Store in storage/app/{folder}
        Storage::disk('public')->putFileAs($folder, $image, $filename);

        return $filename;
    }

    public function deleteImage($fileName, $folder)
    {
        $path = "$folder/$fileName";

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function updateStatus($modelClass, $id, $statusField = 'status')
    {
        // Find record
        $item = $modelClass::find($id);

        if (!$item) {
            return [
                'success' => false,
                'message' => 'Record not found'
            ];
        }

        // Toggle or update status
        $item->{$statusField} = !$item->{$statusField};
        $item->save();

        return [
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $item
        ];
    }
}
