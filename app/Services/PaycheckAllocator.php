<?php

namespace App\Services;

use App\Models\Allocation;
use App\Models\ScheduledItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PaycheckAllocator
{
    public function allocateForUser(User $user, Carbon $start, Carbon $end, bool $forceReallocate = false): array
    {
        $allocationCount = 0;
        $unallocatedExpenses = [];

        $incomeWindowStart = $start->copy()->subDays(60);

        $incomes = $this->incomeQuery($user)
            ->whereBetween('date', [$incomeWindowStart, $end])
            ->orderBy('date')
            ->get();

        $expenses = ScheduledItem::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->where('kind', 'expense')
            ->with('allocationsAsExpense')
            ->orderBy('date')
            ->get();

        foreach ($expenses as $expense) {
            if ($expense->allocationsAsExpense->isNotEmpty() && ! $forceReallocate) {
                continue;
            }

            if ($forceReallocate && $expense->allocationsAsExpense->isNotEmpty()) {
                $expense->allocationsAsExpense()->delete();
            }

            $income = $this->closestIncome($incomes, $expense->date);

            if (! $income) {
                $unallocatedExpenses[] = $expense->id;
                continue;
            }

            Allocation::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'income_scheduled_item_id' => $income->id,
                    'expense_scheduled_item_id' => $expense->id,
                ],
                [
                    'allocated_amount' => $expense->amount,
                ]
            );

            $allocationCount++;
        }

        return [
            'allocated' => $allocationCount,
            'unallocated' => count($unallocatedExpenses),
        ];
    }

    protected function incomeQuery(User $user)
    {
        return ScheduledItem::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('kind', 'income')
                    ->orWhereHas('category', fn ($category) => $category->where('kind', 'income'));
            });
    }

    protected function closestIncome(Collection $incomes, Carbon $expenseDate): ?ScheduledItem
    {
        return $incomes
            ->filter(fn (ScheduledItem $income) => $income->date->lte($expenseDate))
            ->last();
    }

    public function getAllocatedSumForExpense(int $expenseId): float
    {
        return (float) Allocation::where('expense_scheduled_item_id', $expenseId)->sum('allocated_amount');
    }

    public function getRemainingForExpense(ScheduledItem $expense): float
    {
        $allocated = $expense->relationLoaded('allocationsAsExpense')
            ? $expense->allocationsAsExpense->sum('allocated_amount')
            : $this->getAllocatedSumForExpense($expense->id);

        return max(0, (float) $expense->amount - $allocated);
    }
}
