<?php

namespace App\Http\Controllers;

use App\Models\MonthLock;
use App\Services\MonthLockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class MonthLockController extends Controller
{
    public function __construct(private MonthLockService $monthLockService)
    {
    }

    public function index(Request $request): View
    {
        $locks = $this->monthLockService->getLocks($request->user());

        return view('settings.locks', [
            'locks' => $locks,
            'today' => Carbon::today(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $this->monthLockService->lockMonth($request->user(), $validated['month'], $validated['note'] ?? null);

        return redirect()->route('locks.index')->with('success', 'Month locked.');
    }

    public function lockLast(Request $request): RedirectResponse
    {
        $this->monthLockService->lockLastMonth($request->user(), $request->input('note'));

        return redirect()->route('locks.index')->with('success', 'Last month locked.');
    }

    public function destroy(Request $request, MonthLock $lock): RedirectResponse
    {
        $this->monthLockService->unlock($request->user(), $lock);

        return redirect()->route('locks.index')->with('success', 'Lock removed.');
    }
}
