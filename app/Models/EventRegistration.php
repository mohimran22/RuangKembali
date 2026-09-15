<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EventRegistration extends Model
{
    use HasUuids;

    protected $table = 'event_registrations';

    protected $fillable = [
        'id',
        'event_id',
        'user_id',
        'registered_by',
        'transaction_id',
        'ticket_code',
        'status',
        'price',
        'payment_method',
        'registered_at',
        'checked_in_at',
        'checked_in_by'
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function transaction()
{
    return $this->belongsTo(Transaction::class);
}
}
