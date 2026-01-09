<?php

namespace App\Http\Controllers;

use App\Models\ScheduledItem;
use App\Services\LedgerService;
use App\Services\MonthLockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ScheduledItemStatusController extends Controller
{
    public function __construct(private LedgerService $ledgerService, private MonthLockService $monthLockService)
    {
    }

    public function markPaid(Request $request, ScheduledItem $scheduledItem): RedirectResponse
    {
        $this->authorizeItem($request, $scheduledItem);

        if ($this->monthLockService->isLocked($request->user(), $scheduledItem->date)) {
            return back()->with('error', 'This month is locked. Unlock it to change scheduled items.');
        }

        $validated = $request->validate([
            'actual_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $transaction = $this->ledgerService->recordFromScheduledItem(
            $request->user(),
            $scheduledItem,
            $validated['actual_amount'] ?? null,
            $validated['note'] ?? null
        );

        $scheduledItem->update([
            'status' => 'paid',
            'paid_at' => now(),
            'actual_amount' => $validated['actual_amount'] ?? $scheduledItem->actual_amount ?? $scheduledItem->amount,
            'note' => $validated['note'] ?? $scheduledItem->note,
        ]);

        return back()->with('success', 'Scheduled item marked as paid.');
    }

    public function markSkipped(Request $request, ScheduledItem $scheduledItem): RedirectResponse
    {
        $this->authorizeItem($request, $scheduledItem);

        if ($this->monthLockService->isLocked($request->user(), $scheduledItem->date)) {
            return back()->with('error', 'This month is locked. Unlock it to change scheduled items.');
        }

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $scheduledItem->update([
            'status' => 'skipped',
            'paid_at' => null,
            'actual_amount' => null,
            'note' => $validated['note'] ?? $scheduledItem->note,
        ]);

        return back()->with('success', 'Scheduled item marked as skipped.');
    }

    public function markPending(Request $request, ScheduledItem $scheduledItem): RedirectResponse
    {
        $this->authorizeItem($request, $scheduledItem);

        if ($this->monthLockService->isLocked($request->user(), $scheduledItem->date)) {
            return back()->with('error', 'This month is locked. Unlock it to change scheduled items.');
        }

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $scheduledItem->update([
            'status' => ScheduledItem::pendingStatus(),
            'paid_at' => null,
            'actual_amount' => null,
            'note' => $validated['note'] ?? $scheduledItem->note,
        ]);

        return back()->with('success', 'Scheduled item reset to pending.');
    }

    protected function authorizeItem(Request $request, ScheduledItem $scheduledItem): void
    {
        abort_if($scheduledItem->user_id !== $request->user()->id, 403);
    }
}
