<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Support\TransactionFingerprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TransactionImportService
{
    public function parseCsv(string $path): array
    {
        if (!Storage::exists($path)) {
            return [];
        }

        $rows = [];
        $handle = fopen(Storage::path($path), 'r');
        if (!$handle) {
            return $rows;
        }

        $headers = [];
        while (($data = fgetcsv($handle)) !== false) {
            if (empty($headers)) {
                $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $data);
                continue;
            }

            if (count(array_filter($data, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $rows[] = $this->mapRow($headers, $data);
        }

        fclose($handle);

        return array_values(array_filter($rows));
    }

    protected function mapRow(array $headers, array $data): ?array
    {
        $headerString = implode(',', $headers);
        $normalized = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $data);

        if (Str::contains($headerString, ['transaction date', 'post date', 'debit', 'credit'])) {
            return $this->mapFormatB($headers, $normalized);
        }

        if (Str::contains($headerString, ['date', 'description', 'amount'])) {
            return $this->mapFormatA($headers, $normalized);
        }

        return null;
    }

    protected function mapFormatA(array $headers, array $data): ?array
    {
        $row = $this->combineRow($headers, $data);
        if (empty($row['date']) || empty($row['amount'])) {
            return null;
        }

        $amount = (float) $row['amount'];
        $direction = $amount < 0 ? 'debit' : 'credit';

        return [
            'date' => Carbon::parse($row['date'])->toDateString(),
            'description' => $row['description'] ?? '',
            'amount' => abs($amount),
            'direction' => $direction,
        ];
    }

    protected function mapFormatB(array $headers, array $data): ?array
    {
        $row = $this->combineRow($headers, $data);
        $date = $row['transaction date'] ?? $row['post date'] ?? null;
        $debit = isset($row['debit']) ? (float) $row['debit'] : 0;
        $credit = isset($row['credit']) ? (float) $row['credit'] : 0;
        if (!$date || (!$debit && !$credit)) {
            return null;
        }

        $direction = $debit > 0 ? 'debit' : 'credit';
        $amount = $debit > 0 ? $debit : $credit;

        return [
            'date' => Carbon::parse($date)->toDateString(),
            'description' => $row['description'] ?? '',
            'amount' => $amount,
            'direction' => $direction,
        ];
    }

    protected function combineRow(array $headers, array $data): array
    {
        $row = [];
        foreach ($headers as $index => $header) {
            $key = strtolower(trim((string) $header));
            $row[$key] = $data[$index] ?? null;
        }

        return $row;
    }

    public function mapToTransactionData(array $row, Account $target, ?Account $funding, User $user): array
    {
        $type = 'expense';
        $fromAccountId = null;
        $toAccountId = null;
        $accountId = null;
        $amount = (float) $row['amount'];
        $memo = $row['description'] ?? '';

        if ($target->type === 'credit_card') {
            if ($row['direction'] === 'debit') {
                $type = 'credit_charge';
                $accountId = $target->id;
            } else {
                if ($funding) {
                    $type = 'credit_payment';
                    $fromAccountId = $funding->id;
                    $toAccountId = $target->id;
                } else {
                    $type = 'adjustment';
                    $accountId = $target->id;
                }
            }
        } else {
            if ($row['direction'] === 'debit') {
                $type = 'expense';
                $fromAccountId = $target->id;
            } else {
                $type = 'income';
                $toAccountId = $target->id;
            }
        }

        $hash = TransactionFingerprint::compute(
            $user->id,
            $row['date'],
            $type,
            $amount,
            $fromAccountId,
            $toAccountId,
            $accountId,
            $memo
        );

        return [
            'user_id' => $user->id,
            'date' => $row['date'],
            'type' => $type,
            'amount' => $amount,
            'currency' => 'USD',
            'from_account_id' => $fromAccountId,
            'to_account_id' => $toAccountId,
            'account_id' => $accountId,
            'memo' => $memo,
            'hash' => $hash,
        ];
    }

    public function duplicateStatus(User $user, array $transactionData): string
    {
        if (!empty($transactionData['hash']) && Transaction::where('user_id', $user->id)->where('hash', $transactionData['hash'])->exists()) {
            return 'exact';
        }

        $date = Carbon::parse($transactionData['date']);
        $query = Transaction::where('user_id', $user->id)
            ->whereBetween('date', [$date->copy()->subDay(), $date->copy()->addDay()])
            ->where('amount', $transactionData['amount']);

        if ($transactionData['from_account_id']) {
            $query->where('from_account_id', $transactionData['from_account_id']);
        }
        if ($transactionData['to_account_id']) {
            $query->where('to_account_id', $transactionData['to_account_id']);
        }
        if ($transactionData['account_id']) {
            $query->where('account_id', $transactionData['account_id']);
        }

        return $query->exists() ? 'near' : 'none';
    }
}
