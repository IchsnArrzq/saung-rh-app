<?php

namespace App\Models;

use App\Domains\Social\Enums\SpecialRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialRequest extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'table_session_id',
        'table_id',
        'table_code',
        'requested_by',
        'special_request_category_id',
        'description',
        'staff_note',
        'is_paid',
        'price',
        'status',
        // Sisa alur persetujuan manajer yang lama — tidak diisi lagi.
        'approved_by',
        // Staf yang menangani (menandai ditangani/selesai/ditolak). Dasar skor KPI.
        'assigned_to',
        'handled_at',
    ];

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'price' => 'decimal:2',
            'handled_at' => 'datetime',
            'status' => SpecialRequestStatus::class,
        ];
    }

    public function tableSession(): BelongsTo
    {
        return $this->belongsTo(TableSession::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SpecialRequestCategory::class, 'special_request_category_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Masih menunggu atau sedang ditangani.
     *
     * @param  Builder<SpecialRequest>  $query
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', SpecialRequestStatus::openValues());
    }
}
