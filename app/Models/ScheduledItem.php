<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;
use App\Models\SavingsBucket;

class ScheduledItem extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PLANNED = 'planned';

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
        'paid_at',
        'actual_amount',
        'note',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'paid_at' => 'datetime',
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

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public static function pendingStatus(): string
    {
        return Schema::hasColumn('scheduled_items', 'paid_at')
            ? self::STATUS_PENDING
            : self::STATUS_PLANNED;
    }

    public static function pendingStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_PLANNED];
    }
}
