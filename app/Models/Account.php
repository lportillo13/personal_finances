<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creditCard(): HasOne
    {
        return $this->hasOne(CreditCard::class);
    }

    public function recurringRules(): HasMany
    {
        return $this->hasMany(RecurringRule::class);
    }

    public function scheduledItems(): HasMany
    {
        return $this->hasMany(ScheduledItem::class);
    }
}
