<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EventSpeaker extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function speakers()
    {
        return $this->hasMany(EventSpeaker::class);
    }

    public function faqs()
    {
        return $this->hasMany(EventFaq::class);
    }

    public function galleries()
    {
        return $this->hasMany(EventGallery::class);
    }

    public function sponsors()
    {
        return $this->hasMany(EventSponsor::class);
    }

    public function vouchers()
    {
        return $this->hasMany(EventVoucher::class);
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function certificate()
    {
        return $this->hasOne(EventCertificate::class);
    }

    public function getRemainingQuotaAttribute()
    {
        return max(
            0,
            $this->quota - $this->registrations()->count()
        );
    }

    public function getIsRegistrationOpenAttribute()
    {
        $today = now();

        return $this->registration_open <= $today &&
               $this->registration_close >= $today;
    }

    public function getIsFullAttribute()
    {
        return $this->remaining_quota <= 0;
    }
    public function scopePublished($query)
{
    return $query->where('is_published', true);
}

public function scopeUpcoming($query)
{
    return $query->where('start_at', '>', now());
}

public function scopeOngoing($query)
{
    return $query
        ->where('start_at', '<=', now())
        ->where('end_at', '>=', now());
}

public function scopeFinished($query)
{
    return $query->where('end_at', '<', now());
}

public function scopeByStatus($query, $status)
{
    return $query->where('status', $status);
}
public function getStatusLabelAttribute()
{
    $now = now();

    if ($now < $this->registration_open) {
        return 'Coming Soon';
    }

    if (
        $now >= $this->registration_open &&
        $now <= $this->registration_close
    ) {
        return $this->is_full
            ? 'Sold Out'
            : 'Pendaftaran';
    }

    if (
        $now >= $this->start_at &&
        $now <= $this->end_at
    ) {
        return 'Sedang Berlangsung';
    }

    if ($now > $this->end_at) {
        return 'Selesai';
    }

    return 'Coming Soon';
}
}
