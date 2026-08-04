<?php

namespace App\Services\Admin;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    /**
     * Store an image and attach it to the owner. Becomes primary automatically
     * when the owner has no primary image yet.
     */
    public function addImage(Model $owner, UploadedFile $file): Media
    {
        $hasPrimary = $owner->images()->where('is_primary', true)->exists();

        return $this->store($owner, $file, 'image', ! $hasPrimary);
    }

    public function addVideo(Model $owner, UploadedFile $file): Media
    {
        return $this->store($owner, $file, 'video', false);
    }

    public function setPrimaryImage(Model $owner, string $mediaId): void
    {
        $media = $owner->images()->findOrFail($mediaId);

        Media::query()
            ->where('mediable_type', $owner->getMorphClass())
            ->where('mediable_id', $owner->getKey())
            ->where('type', 'image')
            ->update(['is_primary' => false]);

        $media->update(['is_primary' => true]);
    }

    public function delete(Media $media): void
    {
        Storage::disk($media->disk)->delete($media->path);

        $owner = $media->mediable;
        $wasPrimaryImage = $media->is_primary && $media->type === 'image';

        $media->delete();

        // Keep exactly one primary image: promote the next image if we removed it.
        if ($wasPrimaryImage && $owner) {
            $owner->images()->first()?->update(['is_primary' => true]);
        }
    }

    private function store(Model $owner, UploadedFile $file, string $type, bool $isPrimary): Media
    {
        $folder = (string) Str::of(class_basename($owner))->snake()->plural();
        $path = $file->store("media/{$folder}/{$owner->getKey()}", 'public');
        $sort = (int) $owner->media()->where('type', $type)->max('sort_order') + 1;

        return $owner->media()->create([
            'type' => $type,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'sort_order' => $sort,
            'is_primary' => $isPrimary,
        ]);
    }
}
