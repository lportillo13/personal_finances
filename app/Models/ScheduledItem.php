<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\SavingsBucket;

class ScheduledItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recurring_rule_id',
        'date',
        'kind',
        'amount',
        'currency',
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
        'amount' => 'decimal:2',
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

    public function allocationsAsIncome(): HasMany
    {
        return $this->hasMany(Allocation::class, 'income_scheduled_item_id');
    }

    public function allocationsAsExpense(): HasMany
    {
        return $this->hasMany(Allocation::class, 'expense_scheduled_item_id');
    }

    public function savingsBucket(): HasOne
    {
        return $this->hasOne(SavingsBucket::class, 'income_scheduled_item_id');
    }
}
