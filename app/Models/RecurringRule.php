<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'kind',
        'amount',
        'currency',
        'account_id',
        'source_account_id',
        'target_account_id',
        'category_id',
        'frequency',
        'interval',
        'start_date',
        'end_date',
        'occurrences_total',
        'occurrences_remaining',
        'next_run_on',
        'is_active',
        'monthly_day',
        'semimonthly_day_1',
        'semimonthly_day_2',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_run_on' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function scheduledItems(): HasMany
    {
        return $this->hasMany(ScheduledItem::class);
    }
}
