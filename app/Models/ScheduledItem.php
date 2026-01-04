<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recurring_rule_id',
        'date',
        'direction',
        'amount',
        'account_id',
        'source_account_id',
        'target_account_id',
        'category_id',
        'status',
        'posted_at',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'posted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recurringRule(): BelongsTo
    {
        return $this->belongsTo(RecurringRule::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function sourceAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'source_account_id');
    }

    public function targetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'target_account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
