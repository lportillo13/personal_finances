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
        'due_day',
        'statement_close_day',
        'autopay',
        'autopay_account_id',
        'notes',
    ];

    protected $casts = [
        'autopay' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function autopayAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'autopay_account_id');
    }
}
