<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EventCertificate extends Model
{
    use HasUuids;

    protected $table = 'event_certificates';

    protected $fillable = [
        'event_id',
        'template',
        'is_optional',
    ];

    protected $casts = [
        'is_optional' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}