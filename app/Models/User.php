<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'phone',
        'status',
        'navigation_menu_preference',
        'avatar_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'navigation_menu_preference' => 'string',
        ];
    }

    /**
     * URL foto profil, atau null bila belum ada (tampil inisial). Dibangun dari
     * host request yang sedang berjalan — seperti Media::getUrlAttribute — supaya
     * tetap terjangkau saat aplikasi dibuka lewat IP LAN, bukan hanya APP_URL.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->avatar_path
            ? asset('storage/'.ltrim((string) $this->avatar_path, '/'))
            : null);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function tips(): HasMany
    {
        return $this->hasMany(Tip::class, 'waiter_id');
    }

    public function serviceLogs(): HasMany
    {
        return $this->hasMany(ServiceLog::class, 'waiter_id');
    }
}
