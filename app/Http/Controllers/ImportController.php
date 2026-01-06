<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Services\MonthLockService;
use App\Services\TransactionImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function __construct(private TransactionImportService $importService, private MonthLockService $monthLockService)
    {
    }

    public function index(Request $request): View
    {
        $accounts = Account::where('user_id', $request->user()->id)->orderBy('name')->get();

        return view('import.index', [
            'accounts' => $accounts,
        ]);
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $accountIds = Account::where('user_id', $user->id)->pluck('id')->all();

        $validated = $request->validate([
            'file' => ['required', 'file'],
            'account_id' => ['required', 'integer', 'in:' . implode(',', $accountIds)],
            'funding_account_id' => ['nullable', 'integer', 'in:' . implode(',', $accountIds)],
            'source' => ['nullable', 'string', 'max:50'],
        ]);

        $target = Account::where('user_id', $user->id)->findOrFail($validated['account_id']);
        $funding = $validated['funding_account_id'] ? Account::where('user_id', $user->id)->find($validated['funding_account_id']) : null;

        $path = $request->file('file')->store('imports');
        $rows = $this->importService->parseCsv($path);

        if (empty($rows)) {
            Storage::delete($path);
            return back()->with('error', 'Could not read any rows from this file.');
        }

        $prepared = [];
        foreach ($rows as $index => $row) {
            $data = $this->importService->mapToTransactionData($row, $target, $funding, $user);
            $status = $this->importService->duplicateStatus($user, $data);
            $isLocked = $this->monthLockService->isLocked($user, Carbon::parse($data['date']));
            $prepared[] = [
                'index' => $index,
                'row' => $row,
                'data' => $data,
                'duplicate' => $status,
                'locked' => $isLocked,
            ];
        }

        return view('import.preview', [
            'rows' => $prepared,
            'account' => $target,
            'funding' => $funding,
            'source' => $validated['source'] ?? 'csv',
            'filePath' => $path,
        ]);
    }

    public function commit(Request $request): RedirectResponse
    {
        $user = $request->user();
        $accountIds = Account::where('user_id', $user->id)->pluck('id')->all();

        $validated = $request->validate([
            'file_path' => ['required', 'string'],
            'account_id' => ['required', 'integer', 'in:' . implode(',', $accountIds)],
            'funding_account_id' => ['nullable', 'integer', 'in:' . implode(',', $accountIds)],
            'source' => ['nullable', 'string', 'max:50'],
            'selected' => ['nullable', 'array'],
            'selected.*' => ['integer'],
        ]);

        if (!Storage::exists($validated['file_path'])) {
            return redirect()->route('import.index')->with('error', 'Import file no longer exists. Please upload again.');
        }

        $target = Account::where('user_id', $user->id)->findOrFail($validated['account_id']);
        $funding = $validated['funding_account_id'] ? Account::where('user_id', $user->id)->find($validated['funding_account_id']) : null;
        $rows = $this->importService->parseCsv($validated['file_path']);
        $selected = collect($validated['selected'] ?? [])->map(fn ($v) => (int) $v)->all();

        $imported = 0;
        $skippedDuplicates = 0;
        $skippedLocked = 0;

        foreach ($rows as $index => $row) {
            if (!in_array($index, $selected, true)) {
                continue;
            }

            $data = $this->importService->mapToTransactionData($row, $target, $funding, $user);
            $status = $this->importService->duplicateStatus($user, $data);
            if ($status === 'exact') {
                $skippedDuplicates++;
                continue;
            }

            if ($this->monthLockService->isLocked($user, $data['date'])) {
                $skippedLocked++;
                continue;
            }

            $payload = array_merge($data, [
                'source' => $validated['source'] ?? 'csv',
                'imported_at' => now(),
                'external_id' => $row['external_id'] ?? null,
                'is_reconciled' => false,
            ]);

            Transaction::create($payload);
            $imported++;
        }

        Storage::delete($validated['file_path']);

        $message = "$imported transactions imported.";
        if ($skippedDuplicates > 0) {
            $message .= " {$skippedDuplicates} exact duplicates were skipped.";
        }
        if ($skippedLocked > 0) {
            $message .= " {$skippedLocked} entries were skipped due to locked months.";
        }

        return redirect()->route('transactions.index')->with('success', $message);
    }
}
