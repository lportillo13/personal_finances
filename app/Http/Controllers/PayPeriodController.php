<?php

namespace App\Http\Controllers;

use App\Models\ScheduledItem;
use App\Models\SavingsBucket;
use App\Services\PaycheckAllocator;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayPeriodController extends Controller
{
    public function __construct(private PaycheckAllocator $allocator)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $start = $request->filled('from') ? Carbon::parse($request->input('from')) : Carbon::today();
        $end = $request->filled('to') ? Carbon::parse($request->input('to')) : Carbon::today()->addDays(60);
        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $incomes = ScheduledItem::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->where(function ($query) {
                $query->where('kind', 'income')
                    ->orWhereHas('category', fn ($category) => $category->where('kind', 'income'));
            })
            ->with([
                'recurringRule',
                'category',
                'savingsBucket',
                'allocationsAsIncome.expenseItem.recurringRule',
                'allocationsAsIncome.expenseItem.account',
                'allocationsAsIncome.expenseItem.category',
                'allocationsAsIncome.expenseItem.allocationsAsExpense',
            ])
            ->orderBy('date')
            ->get();

        $expenses = ScheduledItem::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->where('kind', 'expense')
            ->with(['recurringRule', 'account', 'category', 'allocationsAsExpense'])
            ->orderBy('date')
            ->get();

        $unallocatedExpenses = $expenses->filter(fn ($expense) => $this->allocator->getRemainingForExpense($expense) > 0);
        $savingsTotal = $incomes->sum(fn ($income) => (float) ($income->savingsBucket->amount ?? 0));

        return view('pay-periods.index', [
            'incomes' => $incomes,
            'unallocatedExpenses' => $unallocatedExpenses,
            'start' => $start,
            'end' => $end,
            'expenses' => $expenses,
            'savingsTotal' => $savingsTotal,
        ]);
    }

    public function allocate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'force_reallocate' => ['sometimes', 'boolean'],
        ]);

        $start = isset($validated['from']) ? Carbon::parse($validated['from']) : Carbon::today();
        $end = isset($validated['to']) ? Carbon::parse($validated['to']) : Carbon::today()->addDays(60);
        $forceReallocate = $request->boolean('force_reallocate');

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $summary = $this->allocator->allocateForUser($request->user(), $start, $end, $forceReallocate);

        return redirect()
            ->route('pay-periods.index', ['from' => $start->toDateString(), 'to' => $end->toDateString()])
            ->with('success', "Allocated {$summary['allocated']} expenses; {$summary['unallocated']} left unallocated.");
    }

    public function saveSavings(Request $request, ScheduledItem $income): RedirectResponse
    {
        if ($income->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        SavingsBucket::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'income_scheduled_item_id' => $income->id,
            ],
            [
                'amount' => $validated['amount'],
                'note' => $validated['note'] ?? null,
            ]
        );

        return redirect()
            ->route('pay-periods.index', $request->only('from', 'to'))
            ->with('success', 'Savings updated for this paycheck.');
    }
}
