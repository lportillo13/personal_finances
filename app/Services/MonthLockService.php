<?php

namespace App\Services;

use App\Models\MonthLock;
use App\Models\User;
use Illuminate\Support\Carbon;

class MonthLockService
{
    public function isLocked(User $user, Carbon|string $date): bool
    {
        $month = Carbon::parse($date)->format('Y-m');

        return MonthLock::where('user_id', $user->id)
            ->where('month', $month)
            ->exists();
    }

    public function lockMonth(User $user, string $month, ?string $note = null): MonthLock
    {
        return MonthLock::updateOrCreate(
            ['user_id' => $user->id, 'month' => $month],
            ['locked_at' => now(), 'note' => $note]
        );
    }

    public function lockLastMonth(User $user, ?string $note = null): MonthLock
    {
        $month = Carbon::today()->subMonth()->format('Y-m');

        return $this->lockMonth($user, $month, $note);
    }

    public function unlock(User $user, MonthLock $lock): void
    {
        if ($lock->user_id !== $user->id) {
            abort(403);
        }

        $lock->delete();
    }

    public function getLocks(User $user)
    {
        return MonthLock::where('user_id', $user->id)
            ->orderByDesc('month')
            ->get();
    }
}
