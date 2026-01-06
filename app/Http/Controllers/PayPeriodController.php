<?php

namespace App\Http\Controllers;

use App\Models\ScheduledItem;
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
        $savingsPerPaycheck = (float) $request->input('savings', 0);

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
                'allocationsAsIncome.expenseItem.recurringRule',
                'allocationsAsIncome.expenseItem.account',
                'allocationsAsIncome.expenseItem.category',
            ])
            ->orderBy('date')
            ->get();

        $unallocatedExpenses = ScheduledItem::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->where('kind', 'expense')
            ->whereDoesntHave('allocationAsExpense')
            ->with(['recurringRule', 'account', 'category'])
            ->orderBy('date')
            ->get();

        return view('pay-periods.index', [
            'incomes' => $incomes,
            'unallocatedExpenses' => $unallocatedExpenses,
            'start' => $start,
            'end' => $end,
            'savingsPerPaycheck' => $savingsPerPaycheck,
        ]);
    }

    public function allocate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $start = isset($validated['from']) ? Carbon::parse($validated['from']) : Carbon::today();
        $end = isset($validated['to']) ? Carbon::parse($validated['to']) : Carbon::today()->addDays(60);

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $summary = $this->allocator->allocateForUser($request->user(), $start, $end);

        return redirect()
            ->route('pay-periods.index', ['from' => $start->toDateString(), 'to' => $end->toDateString()])
            ->with('success', "Allocated {$summary['allocated']} expenses; {$summary['unallocated']} left unallocated.");
    }
}
