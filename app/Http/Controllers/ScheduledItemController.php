<?php

namespace App\Http\Controllers;

use App\Models\RecurringRule;
use App\Models\ScheduledItem;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class ScheduledItemController extends Controller
{
    public function generate(): RedirectResponse
    {
        $horizon = Carbon::today()->addDays(90);
        $created = 0;

        $rules = RecurringRule::where('user_id', auth()->id())
            ->where('is_active', true)
            ->whereDate('next_run_on', '<=', $horizon)
            ->get();

        foreach ($rules as $rule) {
            $nextDate = $rule->next_run_on ?? $rule->start_date;
            $remaining = $rule->occurrences_remaining;

            $date = Carbon::parse($nextDate);
            while (
                $date->lte($horizon) &&
                (!$rule->end_date || $date->lte(Carbon::parse($rule->end_date))) &&
                ($remaining === null || $remaining > 0)
            ) {
                $exists = ScheduledItem::where('recurring_rule_id', $rule->id)
                    ->where('date', $date->toDateString())
                    ->exists();

                if (! $exists) {
                    ScheduledItem::create([
                        'user_id' => $rule->user_id,
                        'recurring_rule_id' => $rule->id,
                        'date' => $date->toDateString(),
                        'direction' => $rule->direction,
                        'amount' => $rule->amount,
                        'account_id' => $rule->account_id,
                        'source_account_id' => $rule->source_account_id,
                        'target_account_id' => $rule->target_account_id,
                        'category_id' => $rule->category_id,
                        'status' => 'planned',
                    ]);
                    $created++;

                    if ($remaining !== null) {
                        $remaining--;
                    }
                }

                $date = $this->calculateNextRun($date, $rule->frequency, $rule->interval ?? 1);
            }

            $rule->update([
                'occurrences_remaining' => $remaining,
                'next_run_on' => $date->toDateString(),
            ]);
        }

        return Redirect::back()->with('status', "Generated {$created} scheduled items.");
    }

    protected function calculateNextRun(Carbon $date, string $frequency, int $interval): Carbon
    {
        return match ($frequency) {
            'weekly' => $date->copy()->addWeeks($interval),
            'biweekly' => $date->copy()->addWeeks(2 * $interval),
            'semimonthly' => $date->copy()->addDays(15 * $interval),
            'monthly' => $date->copy()->addMonths($interval),
            default => $date->copy()->addMonth(),
        };
    }
}
