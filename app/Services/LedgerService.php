<?php

namespace App\Services;

use App\Models\Account;
use App\Models\ScheduledItem;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LedgerService
{
    public function recordFromScheduledItem(User $user, ScheduledItem $item, ?float $actualAmount = null, ?string $memo = null): Transaction
    {
        if ($item->user_id !== $user->id) {
            throw new ModelNotFoundException('Scheduled item not found for user');
        }

        $existing = Transaction::where('user_id', $user->id)
            ->where('scheduled_item_id', $item->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $amount = $actualAmount ?? (float) ($item->actual_amount ?? $item->amount);

        return DB::transaction(function () use ($user, $item, $amount, $memo) {
            $type = null;
            $account = $item->account ?? ($item->account_id ? Account::find($item->account_id) : null);
            $data = [
                'user_id' => $user->id,
                'date' => $item->date,
                'amount' => $amount,
                'currency' => $item->currency,
                'scheduled_item_id' => $item->id,
                'memo' => $memo,
                'source' => 'scheduled_item',
            ];

            if ($item->kind === 'income') {
                $type = 'income';
                $data['to_account_id'] = $item->account_id;
            } elseif ($item->kind === 'expense') {
                if ($account?->type === 'credit_card') {
                    $type = 'credit_charge';
                    $data['account_id'] = $item->account_id;
                } else {
                    $type = 'expense';
                    $data['from_account_id'] = $item->account_id;
                }
            } elseif ($item->kind === 'transfer') {
                $type = 'transfer';
                $data['from_account_id'] = $item->source_account_id;
                $data['to_account_id'] = $item->target_account_id;
            }

            if (!$type) {
                throw new \InvalidArgumentException('Unsupported scheduled item kind.');
            }

            $data['type'] = $type;

            return Transaction::create($data);
        });
    }

    public function computeAccountBalance(Account $account, ?Carbon $asOf = null): float
    {
        $asOfDate = $asOf?->toDateString();
        $queryScope = function ($query) use ($asOfDate) {
            if ($asOfDate) {
                $query->whereDate('date', '<=', $asOfDate);
            }
        };

        $incoming = Transaction::where('user_id', $account->user_id)
            ->where(function ($query) use ($account) {
                $query->where('to_account_id', $account->id)
                    ->orWhere(function ($query) use ($account) {
                        $query->where('account_id', $account->id)
                            ->whereIn('type', ['adjustment']);
                    });
            })
            ->where($queryScope)
            ->sum('amount');

        $outgoing = Transaction::where('user_id', $account->user_id)
            ->where(function ($query) use ($account) {
                $query->where('from_account_id', $account->id)
                    ->orWhere(function ($query) use ($account) {
                        $query->where('account_id', $account->id)
                            ->whereIn('type', ['expense']);
                    });
            })
            ->where($queryScope)
            ->sum('amount');

        $scheduledIncoming = $this->scheduledIncomingForAccount($account, $asOfDate);
        $scheduledOutgoing = $this->scheduledOutgoingForAccount($account, $asOfDate);

        $startingBalance = (float) ($account->current_amount ?? 0);

        return (float) ($startingBalance + $incoming + $scheduledIncoming - $outgoing - $scheduledOutgoing);
    }

    public function computeCreditCardBalance(Account $creditCardAccount, ?Carbon $asOf = null): float
    {
        $asOfDate = $asOf?->toDateString();
        $queryScope = function ($query) use ($asOfDate) {
            if ($asOfDate) {
                $query->whereDate('date', '<=', $asOfDate);
            }
        };

        $charges = Transaction::where('user_id', $creditCardAccount->user_id)
            ->where('account_id', $creditCardAccount->id)
            ->where('type', 'credit_charge')
            ->where($queryScope)
            ->sum('amount');

        $payments = Transaction::where('user_id', $creditCardAccount->user_id)
            ->where('to_account_id', $creditCardAccount->id)
            ->whereIn('type', ['transfer', 'credit_payment'])
            ->where($queryScope)
            ->sum('amount');

        $scheduledCharges = $this->scheduledCreditCharges($creditCardAccount, $asOfDate);
        $scheduledPayments = $this->scheduledCreditPayments($creditCardAccount, $asOfDate);

        return (float) ($charges + $scheduledCharges - $payments - $scheduledPayments);
    }

    protected function scheduledBaseQuery(Account $account, ?string $asOfDate)
    {
        return ScheduledItem::where('user_id', $account->user_id)
            ->whereIn('status', ScheduledItem::pendingStatuses())
            ->whereDoesntHave('transaction')
            ->when($asOfDate, fn ($query) => $query->whereDate('date', '<=', $asOfDate));
    }

    protected function scheduledIncomingForAccount(Account $account, ?string $asOfDate): float
    {
        return (float) $this->scheduledBaseQuery($account, $asOfDate)
            ->where(function ($query) use ($account) {
                $query->where(function ($query) use ($account) {
                    $query->where('kind', 'income')
                        ->where('account_id', $account->id);
                })->orWhere(function ($query) use ($account) {
                    $query->where('kind', 'transfer')
                        ->where('target_account_id', $account->id);
                });
            })
            ->sum('amount');
    }

    protected function scheduledOutgoingForAccount(Account $account, ?string $asOfDate): float
    {
        return (float) $this->scheduledBaseQuery($account, $asOfDate)
            ->where(function ($query) use ($account) {
                $query->where(function ($query) use ($account) {
                    $query->where('kind', 'expense')
                        ->where('account_id', $account->id);
                })->orWhere(function ($query) use ($account) {
                    $query->where('kind', 'transfer')
                        ->where('source_account_id', $account->id);
                });
            })
            ->sum('amount');
    }

    protected function scheduledCreditCharges(Account $creditCardAccount, ?string $asOfDate): float
    {
        return (float) $this->scheduledBaseQuery($creditCardAccount, $asOfDate)
            ->where('kind', 'expense')
            ->where('account_id', $creditCardAccount->id)
            ->sum('amount');
    }

    protected function scheduledCreditPayments(Account $creditCardAccount, ?string $asOfDate): float
    {
        return (float) $this->scheduledBaseQuery($creditCardAccount, $asOfDate)
            ->where('kind', 'transfer')
            ->where('target_account_id', $creditCardAccount->id)
            ->sum('amount');
    }
}
