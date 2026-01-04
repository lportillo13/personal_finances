<?php

namespace App\Http\Controllers;

use App\Models\ScheduledItem;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $start = Carbon::today();
        $end = Carbon::today()->addDays(14);

        $items = ScheduledItem::with(['account', 'category'])
            ->where('user_id', auth()->id())
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->orderBy('amount', 'desc')
            ->get()
            ->groupBy(fn ($item) => $item->date->toDateString());

        $incomeTotal = ScheduledItem::where('user_id', auth()->id())
            ->whereBetween('date', [$start, $end])
            ->where('direction', 'income')
            ->sum('amount');

        $expenseTotal = ScheduledItem::where('user_id', auth()->id())
            ->whereBetween('date', [$start, $end])
            ->where('direction', 'expense')
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
