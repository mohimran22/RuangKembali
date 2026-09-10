<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventFaq extends Model
{
    use HasUuid;

    protected $table = 'event_faqs';

    protected $fillable = [
        'event_id',
        'question',
        'answer',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Event pemilik FAQ
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}