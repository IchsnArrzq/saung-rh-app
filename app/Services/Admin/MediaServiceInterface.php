<?php

namespace App\Services\Admin;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

/**
 * Shared media-library operations for any model using the HasMedia trait.
 */
interface MediaServiceInterface
{
    public function addImage(Model $owner, UploadedFile $file): Media;

    public function addVideo(Model $owner, UploadedFile $file): Media;

    public function setPrimaryImage(Model $owner, string $mediaId): void;

    public function delete(Media $media): void;
}
