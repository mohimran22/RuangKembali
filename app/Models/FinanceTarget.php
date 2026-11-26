<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceTarget extends Model
{
    protected $table = 'finance_targets';

    protected $fillable = ['year', 'month', 'target'];

    protected $casts = [
        'year'   => 'integer',
        'month'  => 'integer',
        'target' => 'decimal:2',
    ];
}