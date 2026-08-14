<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EventRundown extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_id',
        'rundown_date',
        'start_time',
        'end_time',
        'activity',
        'description',
        'speaker',
        'location',
        'sort_order',
    ];

    protected $casts = [
        'rundown_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
