<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AccountingJournalEnclosure extends Model
{
    use HasUuids;

    protected $table = 'accounting_journal_enclosures';

    protected $fillable = [
        'journal_id',
        'file_name',
    ];

    public function journal()
    {
        return $this->belongsTo(AccountingJournal::class, 'journal_id');
    }
}
