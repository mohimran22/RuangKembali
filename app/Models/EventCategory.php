<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EventCategory extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active'
    ];

    public function events()
    {
        return $this->hasMany(Event::class)->latest();
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
}
