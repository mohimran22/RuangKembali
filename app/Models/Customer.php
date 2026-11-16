<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function province() {

    return $this->belongsTo(Province::class);
}

    public function city() {

    return $this->belongsTo(City::class);
}

    public function district() {

    return $this->belongsTo(District::class);
}

    public function subDistrict() {

    return $this->belongsTo(SubDistrict::class);
}

    public function postalCode()
{
    return $this->belongsTo(PostalCode::class, 'postal_code_id');
}

public function registrations()
{
    return $this->hasMany(EventRegistration::class, 'user_id', 'user_id');
}

public function scopeLoyalty($query, $level)
    {
        return $query->where('loyalty_level', $level);
    }

    public function getLoyaltyLevelFormattedAttribute()
    {
        return ucfirst($this->loyalty_level);
    }

    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    public function getDisplayNameAttribute()
    {
        return $this->user?->fullname;
    }

public static function generateNic(): string
{
    // Serialisasi pembuatan NIC. Lepas otomatis saat transaksi commit/rollback.
    DB::select("SELECT pg_advisory_xact_lock(hashtext('generate_nic'))");

    $lastNumber = self::whereRaw("nic ~ '^M-[0-9]+$'")
        ->selectRaw('MAX(CAST(SUBSTRING(nic FROM 3) AS INTEGER)) as max_nic')
        ->value('max_nic');

    return 'M-' . str_pad(((int) $lastNumber) + 1, 4, '0', STR_PAD_LEFT);
}


    public static function getDefaultAttributes($user)
    {
        return [
            'nic' => self::generateNic(),
            'province_id' => 15,
            'city_id' => 234,
            'district_id' => 3372,
            'sub_district_id' => 42178,
            'postal_code_id' => 42178,
            'loyalty_level' => 1,

        ];
    }
// Level otomatis: 1-3 dihitung dari event, 4-6 (Silver/Gold/Platinum) tetap manual
public const LEVEL_SILVER   = 2; // jumlah event lunas minimal
public const LEVEL_GOLD     = 3;
public const LEVEL_PLATINUM = 5;

public static function levelSql(): string
{
    $paid = "(SELECT COUNT(DISTINCT er.event_id) FROM event_registrations er
              WHERE er.user_id = customers.user_id
                AND er.status IN ('paid','attended'))";
    $any  = "EXISTS (SELECT 1 FROM event_registrations er
              WHERE er.user_id = customers.user_id)";

    $s = self::LEVEL_SILVER; $g = self::LEVEL_GOLD; $p = self::LEVEL_PLATINUM;

    return "GREATEST(COALESCE(customers.loyalty_level, 1), CASE
        WHEN $paid >= $p THEN 6
        WHEN $paid >= $g THEN 5
        WHEN $paid >= $s THEN 4
        WHEN $paid >= 1 THEN 3
        WHEN $any THEN 2
        ELSE 1
    END)";
}

public function getEffectiveLoyaltyLevelAttribute(): int
{
    if (isset($this->attributes['auto_level'])) {
        return (int) $this->attributes['auto_level'];
    }

    $regs = $this->registrations()->get(['event_id', 'status']);
    $paid = $regs->whereIn('status', ['paid', 'attended'])->pluck('event_id')->unique()->count();

    $auto = match (true) {
        $paid >= self::LEVEL_PLATINUM => 6,
        $paid >= self::LEVEL_GOLD     => 5,
        $paid >= self::LEVEL_SILVER   => 4,
        $paid >= 1                    => 3,
        $regs->isNotEmpty()           => 2,
        default                       => 1,
    };

    return max((int) $this->loyalty_level, $auto);
}
public function getReadableLoyaltyLevelAttribute()
{
    return [
        1 => 'Lead',
        2 => 'Prospek',
        3 => 'New Member',
        4 => 'Silver',
        5 => 'Gold',
        6 => 'Platinum',
    ][$this->effective_loyalty_level] ?? 'Tidak diketahui';
}

    public function getReadableCategoryAttribute()
    {
    return [
        1 => 'Individu',
        2 => 'Perusahaan',
        3 => 'Instansi',
        4 => 'Lainnya',
    ][$this->category] ?? 'Tidak diketahui';
    }
//     public function getLoyaltyLevelAttribute()
// {
//     $totalProject = $this->projects()->count();

//     return match (true) {
//         $totalProject === 0 => 'Lead',
//         $totalProject <= 2 => 'New Customer',
//         $totalProject <= 5 => 'Silver',
//         $totalProject <= 10 => 'Gold',
//         $totalProject <= 20 => 'Platinum',
//         default => 'Lead',
//     };
// }

public const CHANNELS = [
    'instagram' => 'Instagram',
    'whatsapp'  => 'WhatsApp',
    'facebook'  => 'Facebook',
    'tiktok'    => 'TikTok',
    'google'    => 'Google / Website',
    'referral'  => 'Referensi / Teman',
    'event'     => 'Event / Pameran',
    'other'     => 'Lainnya',
];

public function getReadableChannelAttribute()
{
    return self::CHANNELS[$this->channel] ?? '-';
}
    protected function displayNameWithTitle(): Attribute
{
    return Attribute::make(
        get: function () {
            $name = $this->display_name ?? '';

            $gender = optional($this->user)->gender;

            return match ($gender) {
                1 => 'Bapak ' . $name,
                2 => 'Ibu ' . $name,
                default => $name,
            };
        }
    );
}


    use HasFactory, HasUuids;

    protected $table = 'customers';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'nic',
        'category',
        'loyalty_level',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'province_id',
        'city_id',
        'district_id',
        'sub_district_id',
        'postal_code_id',
        'notes',
        'is_active',
        'signature',
        'channel'
    ];

}