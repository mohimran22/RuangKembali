<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Partner extends Model
{

    public function user()
{
    return $this->belongsTo(User::class);
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

public function scopeLoyalty($query, $level)
    {
        return $query->where('loyalty_level', $level);
    }

    /**
     * 🔹 Accessor: Format membership jadi huruf besar pertama (Silver, Gold, Platinum)
     */
    public function getLoyaltyLevelFormattedAttribute()
    {
        return ucfirst($this->loyalty_level);
    }

    /**
     * 🔹 Accessor: Status aktif/nonaktif readable
     */
    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    public static function generateArchitectId()
    {
        $lastNumber = self::selectRaw("MAX(CAST(SUBSTRING(architect_id, 3) AS INTEGER)) as max_architect_id")->value('max_architect_id');
        $newNumber = ($lastNumber ?? 0) + 1;

        return 'MIT-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function getDefaultAttributes($user)
    {
        return [
            'architect_id' => self::generateArchitectId(),
            'province_id' => 15,
            'city_id' => 234,
            'district_id' => 3372,
            'sub_district_id' => 42178,
            'postal_code_id' => 42178,
            'architect_name' => 'Ruang Kembali',
            'architect_phone' => '087652',
            'architect_address' => 'Jawa Timur',
        ];
    }

    public function getReadableLoyaltyLevelAttribute()
    {
    return [
        1 => 'Silver',
        2 => 'Gold',
        3 => 'Platinum',
    ][$this->loyalty_level] ?? 'Tidak diketahui';
    }

    public function getReadableCategoryAttribute()
    {
    return [
        1 => 'Individu',
        2 => 'Perusahaan',
        3 => 'Instansi',
        3 => 'Lainnya',
    ][$this->loyalty_level] ?? 'Tidak diketahui';
    }

    use HasFactory, HasUuids;

    protected $table = 'architects';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'architect_id',
        'architect_name',
        'architect_phone',
        'architect_address',
        'province_id',
        'city_id',
        'district_id',
        'sub_district_id',
        'postal_code_id',
        'notes',
        'is_active',
    ];

}
