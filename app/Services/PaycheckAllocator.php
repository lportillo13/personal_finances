<?php

namespace App\Services;

use App\Models\Allocation;
use App\Models\ScheduledItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PaycheckAllocator
{
    public function allocateForUser(User $user, Carbon $start, Carbon $end): array
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
            ->with('allocationAsExpense')
            ->orderBy('date')
            ->get();

        foreach ($expenses as $expense) {
            $income = $this->closestIncome($incomes, $expense->date);

            if (! $income) {
                if ($expense->allocationAsExpense) {
                    $expense->allocationAsExpense->delete();
                }
                $unallocatedExpenses[] = $expense->id;
                continue;
            }

            Allocation::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'expense_scheduled_item_id' => $expense->id,
                ],
                [
                    'income_scheduled_item_id' => $income->id,
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
}
