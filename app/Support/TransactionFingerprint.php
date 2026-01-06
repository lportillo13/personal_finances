<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class TransactionFingerprint
{
    public static function compute(
        int $userId,
        Carbon|string $date,
        string $type,
        float $amount,
        ?int $fromAccountId,
        ?int $toAccountId,
        ?int $accountId,
        ?string $memo
    ): string {
        $normalizedMemo = self::normalizeMemo($memo ?? '');
        $dateString = Carbon::parse($date)->toDateString();

        $pieces = [
            $userId,
            $dateString,
            strtolower($type),
            number_format($amount, 2, '.', ''),
            $fromAccountId ?? 'null',
            $toAccountId ?? 'null',
            $accountId ?? 'null',
            $normalizedMemo,
        ];

        return hash('sha256', implode('|', $pieces));
    }

    public static function normalizeMemo(string $memo): string
    {
        $lower = strtolower(trim($memo));
        $stripped = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $lower);
        $collapsed = preg_replace('/\s+/', ' ', $stripped ?? '');

        return trim($collapsed ?? '');
    }
}
