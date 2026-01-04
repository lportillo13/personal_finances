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
            ->orderBy('date')
            ->get()
            ->groupBy(fn ($item) => $item->date->toDateString());

        $incomeTotal = ScheduledItem::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->where('kind', 'income')
            ->sum('amount');

        $expenseTotal = ScheduledItem::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->where('kind', 'expense')
            ->sum('amount');

        return view('dashboard', [
            'groupedItems' => $items,
            'incomeTotal' => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'start' => $start,
            'end' => $end,
        ]);
    }
}
