<?php

namespace App\Models;

use App\Services\VoucherService;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EventVoucher extends Model
{
    use HasUuid;   // pakai trait UUID yang sama dengan model lain di projectmu

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'event_vouchers';

    protected $fillable = [
        'voucher_number', 'code', 'name', 'event_id', 'type', 'value', 'max_discount',
        'quota', 'per_user_limit', 'valid_from', 'valid_until', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'valid_from'  => 'datetime',
            'valid_until' => 'datetime',
            'is_active'   => 'boolean',
            'value'       => 'decimal:2',
            'quota'       => 'integer',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'voucher_id');
    }

    public static function generateNumber(): string
    {
        // Serialisasi pembuatan nomor. Lepas otomatis saat transaksi selesai.
        DB::select("SELECT pg_advisory_xact_lock(hashtext('generate_voucher_number'))");

        $last = self::query()
            ->whereRaw("voucher_number ~ '^VCH-[0-9]+$'")
            ->selectRaw('MAX(CAST(SUBSTRING(voucher_number FROM 5) AS INTEGER)) AS max_no')
            ->value('max_no');

        return 'VCH-' . str_pad(((int) $last) + 1, 4, '0', STR_PAD_LEFT);
    }

    public function hasBeenUsed(): bool
    {
        return $this->transactions()->exists();
    }

    public function getUsedCountAttribute(): int
    {
        return VoucherService::usedCount($this);
    }

    public function getValueLabelAttribute(): string
    {
        $v = (float) $this->value;

        if ($this->type === 'percent') {
            return (floor($v) == $v ? number_format($v, 0, ',', '.') : number_format($v, 2, ',', '.')) . '%';
        }

        return 'Rp ' . number_format($v, 0, ',', '.');
    }

    public function getStatusKeyAttribute(): string
    {
        return match (true) {
            ! $this->is_active                                               => 'inactive',
            $this->valid_from && now()->lt($this->valid_from)                => 'upcoming',
            $this->valid_until && now()->gt($this->valid_until)              => 'expired',
            $this->quota !== null && $this->used_count >= $this->quota       => 'exhausted',
            default                                                          => 'active',
        };
    }
}