<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsBucket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'income_scheduled_item_id',
        'amount',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function incomeItem(): BelongsTo
    {
        return $this->belongsTo(ScheduledItem::class, 'income_scheduled_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
