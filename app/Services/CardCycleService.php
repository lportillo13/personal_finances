<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CardCycleService
{
    public function getCurrentCycle(Account $cardAccount, Carbon $asOf): array
    {
        $creditCard = $this->getCard($cardAccount);
        $closeDay = $this->getCloseDay($creditCard);

        $candidateEnd = $this->setDayClamped($asOf->copy(), $closeDay);
        $periodEnd = $asOf->lessThanOrEqualTo($candidateEnd)
            ? $candidateEnd
            : $this->setDayClamped($asOf->copy()->addMonthNoOverflow(), $closeDay);

        $previousEnd = $this->setDayClamped($periodEnd->copy()->subMonthNoOverflow(), $closeDay);
        $periodStart = $previousEnd->copy()->addDay();

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'due_date' => $this->computeDueDate($creditCard, $periodEnd),
        ];
    }

    public function getPreviousCycle(Account $cardAccount, Carbon $asOf): array
    {
        $creditCard = $this->getCard($cardAccount);
        $closeDay = $this->getCloseDay($creditCard);
        $currentCycle = $this->getCurrentCycle($cardAccount, $asOf);

        $periodEnd = $this->setDayClamped($currentCycle['period_end']->copy()->subMonthNoOverflow(), $closeDay);
        $previousEnd = $this->setDayClamped($periodEnd->copy()->subMonthNoOverflow(), $closeDay);
        $periodStart = $previousEnd->copy()->addDay();

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'due_date' => $this->computeDueDate($creditCard, $periodEnd),
        ];
    }

    public function computeStatementBalance(Account $cardAccount, Carbon $periodStart, Carbon $periodEnd): float
    {
        $creditCard = $this->getCard($cardAccount);
        $dueDate = $this->computeDueDate($creditCard, $periodEnd);

        $charges = Transaction::where('user_id', $cardAccount->user_id)
            ->where('account_id', $cardAccount->id)
            ->where('type', 'credit_charge')
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->sum('amount');

        $payments = Transaction::where('user_id', $cardAccount->user_id)
            ->where('to_account_id', $cardAccount->id)
            ->whereIn('type', ['transfer', 'credit_payment'])
            ->whereBetween('date', [$periodEnd->copy()->addDay()->toDateString(), $dueDate->toDateString()])
            ->sum('amount');

        return (float) ($charges - $payments);
    }

    public function computeCurrentBalance(Account $cardAccount, ?Carbon $asOf = null): float
    {
        $this->getCard($cardAccount);
        $asOfDate = $asOf?->toDateString();
        $scope = function ($query) use ($asOfDate) {
            if ($asOfDate) {
                $query->whereDate('date', '<=', $asOfDate);
            }
        };

        $charges = Transaction::where('user_id', $cardAccount->user_id)
            ->where('account_id', $cardAccount->id)
            ->where('type', 'credit_charge')
            ->where($scope)
            ->sum('amount');

        $payments = Transaction::where('user_id', $cardAccount->user_id)
            ->where('to_account_id', $cardAccount->id)
            ->whereIn('type', ['transfer', 'credit_payment'])
            ->where($scope)
            ->sum('amount');

        return (float) ($charges - $payments);
    }

    protected function getCard(Account $cardAccount)
    {
        if ($cardAccount->type !== 'credit_card' || ! $cardAccount->creditCard) {
            throw new ModelNotFoundException('Credit card account not found.');
        }

        return $cardAccount->creditCard;
    }

    protected function getCloseDay($creditCard): int
    {
        return (int) ($creditCard->statement_close_day ?? $creditCard->due_day ?? 1);
    }

    protected function computeDueDate($creditCard, Carbon $periodEnd): Carbon
    {
        $dueDay = (int) ($creditCard->payment_due_day ?? $creditCard->due_day ?? 25);
        $dueMonth = $periodEnd->copy()->addMonthNoOverflow();

        return $this->setDayClamped($dueMonth, $dueDay);
    }

    protected function setDayClamped(Carbon $date, int $day): Carbon
    {
        $clampedDay = min($day, $date->daysInMonth);

        return $date->copy()->day($clampedDay);
    }
}
