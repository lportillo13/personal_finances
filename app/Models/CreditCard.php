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
        'payment_due_day',
        'statement_close_day',
        'minimum_payment',
        'current_amount',
        'autopay_enabled',
        'autopay_mode',
        'autopay_fixed_amount',
        'autopay_pay_from_account_id',
        'default_funding_account_id',
        'notes',
    ];

    protected $casts = [
        'autopay_enabled' => 'boolean',
        'minimum_payment' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'autopay_fixed_amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function autopayPayFrom(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'autopay_pay_from_account_id');
    }

    public function defaultFundingAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'default_funding_account_id');
    }
}
