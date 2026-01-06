<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'locked_at',
        'note',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
