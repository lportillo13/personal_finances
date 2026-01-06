<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Allocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'income_scheduled_item_id',
        'expense_scheduled_item_id',
        'allocated_amount',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
    ];

    public function incomeItem(): BelongsTo
    {
        return $this->belongsTo(ScheduledItem::class, 'income_scheduled_item_id');
    }

    public function expenseItem(): BelongsTo
    {
        return $this->belongsTo(ScheduledItem::class, 'expense_scheduled_item_id');
    }
}
