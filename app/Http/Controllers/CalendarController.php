<?php

namespace App\Http\Controllers;

use App\Models\ScheduledItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $monthString = $request->input('month', Carbon::today()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $monthString)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $items = ScheduledItem::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->with(['category', 'allocationsAsExpense'])
            ->orderBy('date')
            ->get()
            ->groupBy(fn ($item) => $item->date->toDateString());

        $weeks = [];
        $cursor = $start->copy()->startOfWeek();
        $endCursor = $end->copy()->endOfWeek();
        while ($cursor->lte($endCursor)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
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
        ]);
    }

    public function day(Request $request): View
    {
        $user = $request->user();
        $date = $request->filled('date') ? Carbon::parse($request->input('date')) : Carbon::today();

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
}
