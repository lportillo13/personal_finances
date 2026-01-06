<?php

namespace App\Http\Controllers;

use App\Models\Allocation;
use App\Models\ScheduledItem;
use App\Services\PaycheckAllocator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AllocationController extends Controller
{
    public function __construct(private PaycheckAllocator $allocator)
    {
    }

    public function reassign(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'allocation_id' => ['required', 'exists:allocations,id'],
            'target_income_scheduled_item_id' => ['required', 'exists:scheduled_items,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $allocation = Allocation::where('user_id', $request->user()->id)
            ->with('expenseItem.allocationsAsExpense')
            ->findOrFail($data['allocation_id']);

        $targetIncome = ScheduledItem::where('user_id', $request->user()->id)
            ->findOrFail($data['target_income_scheduled_item_id']);

        $expense = $allocation->expenseItem;
        $currentTotal = $expense->allocationsAsExpense->sum('allocated_amount');

        if ($currentTotal > (float) $expense->amount + 0.01) {
            return back()->with('error', 'This expense is already over-allocated.');
        }

        $amount = (float) $data['amount'];

        if ($amount > (float) $allocation->allocated_amount) {
            return back()->with('error', 'Amount exceeds the current allocation.');
        }

        $existingTarget = Allocation::where('user_id', $request->user()->id)
            ->where('expense_scheduled_item_id', $expense->id)
            ->where('income_scheduled_item_id', $targetIncome->id)
            ->first();

        if ($amount === (float) $allocation->allocated_amount) {
            if ($existingTarget) {
                $existingTarget->allocated_amount += $amount;
                $existingTarget->save();
                $allocation->delete();
            } else {
                $allocation->income_scheduled_item_id = $targetIncome->id;
                $allocation->save();
            }
        } else {
            $allocation->allocated_amount -= $amount;
            $allocation->save();

            if ($existingTarget) {
                $existingTarget->allocated_amount += $amount;
                $existingTarget->save();
            } else {
                Allocation::create([
                    'user_id' => $request->user()->id,
                    'income_scheduled_item_id' => $targetIncome->id,
                    'expense_scheduled_item_id' => $expense->id,
                    'allocated_amount' => $amount,
                ]);
            }
        }

        return back()->with('success', 'Allocation updated.');
    }

    public function split(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'expense_scheduled_item_id' => ['required', 'exists:scheduled_items,id'],
            'income_scheduled_item_id' => ['required', 'exists:scheduled_items,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $expense = ScheduledItem::where('user_id', $request->user()->id)
            ->where('id', $data['expense_scheduled_item_id'])
            ->with('allocationsAsExpense')
            ->firstOrFail();

        $income = ScheduledItem::where('user_id', $request->user()->id)
            ->findOrFail($data['income_scheduled_item_id']);

        $remaining = $this->allocator->getRemainingForExpense($expense);
        $amount = (float) $data['amount'];

        if ($amount > $remaining) {
            return back()->with('error', 'Amount exceeds the remaining balance for this expense.');
        }

        $allocation = Allocation::firstOrNew([
            'user_id' => $request->user()->id,
            'income_scheduled_item_id' => $income->id,
            'expense_scheduled_item_id' => $expense->id,
        ]);

        $allocation->allocated_amount = ($allocation->allocated_amount ?? 0) + $amount;
        $allocation->save();

        return back()->with('success', 'Expense allocation saved.');
    }
}
