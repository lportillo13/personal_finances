<?php

namespace App\Http\Controllers;

use App\Models\ScheduledItem;
use App\Services\CategoryInitializer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private CategoryInitializer $categoryInitializer)
    {
    }

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $this->categoryInitializer->ensureDefaults($user);

        $start = Carbon::today();
        $end = Carbon::today()->addDays(30);

        $items = ScheduledItem::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->with(['recurringRule', 'category', 'account'])
            ->orderBy('date')
            ->get()
            ->groupBy(fn ($item) => $item->date->toDateString());

        $totalsQuery = ScheduledItem::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->whereIn('kind', ['income', 'expense']);

        $incomeTotal = (clone $totalsQuery)->where('kind', 'income')->sum('amount');
        $expenseTotal = (clone $totalsQuery)->where('kind', 'expense')->sum('amount');

        return view('dashboard', [
            'groupedItems' => $items,
            'incomeTotal' => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'netTotal' => $incomeTotal - $expenseTotal,
            'start' => $start,
            'end' => $end,
        ]);
    }
}
