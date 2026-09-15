<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class AccountingJournalDetail extends Model
{
    use HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $table = 'accounting_journal_details';

    protected $fillable = [
        'journal_id',
        'account_id',
        'person',
        'debit',
        'credit',
        'description',
    ];

     public function journal()
    {
        return $this->belongsTo(AccountingJournal::class, 'journal_id');
    }

    public function account()
    {
        return $this->belongsTo(AccountingAccount::class, 'account_id');
    }
    public function getPersonNameAttribute()
    {
        $akunOtomatisPusat = ['K 0026', 'K 0027', 'K 0031', 'K 0032'];

        if (in_array($this->account->account_code ?? '', $akunOtomatisPusat)) {
            return \App\Models\User::find($this->person)?->fullname ?: '-';
        }

        $result = match ($this->account->person_type) {
            'member' => Str::isUuid($this->person)
                ? Customer::find($this->person)?->displayName
                : $this->person,

            'team' => Str::isUuid($this->person)
                ? Team::find($this->person)?->displayName
                : $this->person,

            'mitra' => Str::isUuid($this->person)
                ? Partner::find($this->person)?->displayName
                : $this->person,

            'license' => Str::isUuid($this->person)
                ? License::find($this->person)?->name
                : $this->person,

            default => $this->person,
        };

        return blank($result) ? '-' : $result;
    }

}
