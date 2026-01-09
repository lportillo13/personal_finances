<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ScheduledItem;
use App\Models\User;
use App\Services\CardCycleService;
use App\Services\LedgerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(
        Request $request,
        LedgerService $ledgerService,
        CardCycleService $cardCycleService
    ): View
    {
        $user = $request->user();
        $monthString = $request->input('month', Carbon::today()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $monthString)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $this->ensureCreditCardDueItems($user, $start, $end, $cardCycleService);

        $fundingAccount = Account::where('user_id', $user->id)
            ->where('is_funding', true)
            ->first();

        $items = ScheduledItem::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->with(['category', 'allocationsAsExpense'])
            ->orderBy('date')
            ->get()
            ->groupBy(fn ($item) => $item->date->toDateString());

        $weeks = [];
        $fundingBalances = [];
        $cursor = $start->copy()->startOfWeek();
        $endCursor = $end->copy()->endOfWeek();
        while ($cursor->lte($endCursor)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                if ($fundingAccount) {
                    $fundingBalances[$cursor->toDateString()] = $ledgerService->computeAccountBalance($fundingAccount, $cursor);
                }
                $week[] = $cursor->copy();
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        return view('calendar.index', [
            'start' => $start,
            'end' => $end,
            'weeks' => $weeks,
            'items' => $items,
            'fundingAccount' => $fundingAccount,
            'fundingBalances' => $fundingBalances,
        ]);
    }

    public function day(Request $request, CardCycleService $cardCycleService): View
    {
        $user = $request->user();
        $date = $request->filled('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $this->ensureCreditCardDueItems(
            $user,
            $date->copy()->startOfMonth(),
            $date->copy()->endOfMonth(),
            $cardCycleService
        );

        $items = ScheduledItem::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->with(['category', 'allocationsAsExpense'])
            ->orderBy('kind')
            ->orderBy('date')
            ->get();

        $incomeOptions = ScheduledItem::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('kind', 'income')
                    ->orWhereHas('category', fn ($category) => $category->where('kind', 'income'));
            })
            ->orderBy('date')
            ->get();

        $suggestedIncome = $incomeOptions->filter(fn (ScheduledItem $income) => $income->date->lte($date))->last();

        return view('calendar.day', [
            'date' => $date,
            'items' => $items,
            'incomeOptions' => $incomeOptions,
            'suggestedIncome' => $suggestedIncome,
        ]);
    }

    protected function ensureCreditCardDueItems(
        User $user,
        Carbon $start,
        Carbon $end,
        CardCycleService $cardCycleService
    ): void {
        $cardAccounts = Account::where('user_id', $user->id)
            ->where('type', 'credit_card')
            ->with('creditCard')
            ->get();

        if ($cardAccounts->isEmpty()) {
            return;
        }

        $monthCursor = $start->copy()->startOfMonth();
        $endMonth = $end->copy()->startOfMonth();

        while ($monthCursor->lte($endMonth)) {
            $asOf = $monthCursor->copy()->endOfMonth();
            foreach ($cardAccounts as $cardAccount) {
                $cycle = $cardCycleService->getPreviousCycle($cardAccount, $asOf);
                $dueDate = $cycle['due_date'];

                if (! $dueDate->between($start, $end)) {
                    continue;
                }

                $statementBalance = $cardCycleService->computeStatementBalance(
                    $cardAccount,
                    $cycle['period_start'],
                    $cycle['period_end']
                );

                if ($statementBalance <= 0) {
                    continue;
                }

                $existing = ScheduledItem::where('user_id', $user->id)
                    ->where('kind', 'transfer')
                    ->whereDate('date', $dueDate)
                    ->where('target_account_id', $cardAccount->id)
                    ->first();

                if ($existing) {
                    continue;
                }

                ScheduledItem::create([
                    'user_id' => $user->id,
                    'date' => $dueDate,
                    'kind' => 'transfer',
                    'amount' => $statementBalance,
                    'currency' => $cardAccount->currency ?? 'USD',
                    'target_account_id' => $cardAccount->id,
                    'status' => ScheduledItem::pendingStatus(),
                    'notes' => "{$cardAccount->name} payment due",
                ]);
            }

            $monthCursor->addMonth();
        }
    }
}
