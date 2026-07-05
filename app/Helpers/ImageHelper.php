<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class ImageHelper
{
    public static function uploadAvatar(UploadedFile $file, string $folder): string {

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/' . $folder), $filename);
        return 'uploads/' . $folder . '/' . $filename;
    }
}
