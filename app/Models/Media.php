<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'media';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'type',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'sort_order',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Disk `public` dilayani lewat symlink public/storage, jadi URL-nya dibangun
     * dari host request yang sedang berjalan (asset()), bukan dari APP_URL
     * seperti Storage::url(). Tablet pelayan dan ponsel tamu yang membuka
     * aplikasi lewat IP LAN tidak bisa menjangkau host APP_URL — di sana setiap
     * foto menu yang diunggah dulu berakhir 404.
     */
    public function getUrlAttribute(): string
    {
        if ($this->disk === 'public') {
            return asset('storage/'.ltrim($this->path, '/'));
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }
}
