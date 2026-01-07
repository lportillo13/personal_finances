<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\TransactionFingerprint;
use Illuminate\Support\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'type',
        'amount',
        'currency',
        'from_account_id',
        'to_account_id',
        'account_id',
        'scheduled_item_id',
        'memo',
        'statement_period_start',
        'statement_period_end',
        'external_id',
        'source',
        'imported_at',
        'reconciled_at',
        'is_reconciled',
        'hash',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'statement_period_start' => 'date',
        'statement_period_end' => 'date',
        'imported_at' => 'datetime',
        'reconciled_at' => 'datetime',
        'is_reconciled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $transaction) {
            if (empty($transaction->currency)) {
                $transaction->currency = 'USD';
            }

            if (!$transaction->hash && $transaction->user_id && $transaction->date && $transaction->type && $transaction->amount) {
                $transaction->hash = TransactionFingerprint::compute(
                    (int) $transaction->user_id,
                    Carbon::parse($transaction->date),
                    (string) $transaction->type,
                    (float) $transaction->amount,
                    $transaction->from_account_id ? (int) $transaction->from_account_id : null,
                    $transaction->to_account_id ? (int) $transaction->to_account_id : null,
                    $transaction->account_id ? (int) $transaction->account_id : null,
                    $transaction->memo
                );
            }
        });

        static::deleting(function (self $transaction) {
            if (! $transaction->scheduled_item_id) {
                return;
            }

            $scheduledItem = $transaction->scheduledItem()->first();

            if (! $scheduledItem) {
                return;
            }

            $scheduledItem->allocationsAsIncome()->delete();
            $scheduledItem->allocationsAsExpense()->delete();
            $scheduledItem->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function scheduledItem(): BelongsTo
    {
        return $this->belongsTo(ScheduledItem::class);
    }
}
