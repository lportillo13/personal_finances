<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'issuer_name',
        'last4',
        'due_day',
        'statement_close_day',
        'autopay_enabled',
        'autopay_pay_from_account_id',
        'notes',
    ];

    protected $casts = [
        'autopay_enabled' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function autopayPayFrom(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'autopay_pay_from_account_id');
    }
}
