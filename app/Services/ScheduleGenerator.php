<?php

namespace App\Services;

use App\Models\RecurringRule;
use App\Models\ScheduledItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleGenerator
{
    public function generateForUser(User $user, Carbon $start, Carbon $end): int
    {
        $created = 0;

        $rules = RecurringRule::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        foreach ($rules as $rule) {
            $created += $this->generateForRule($rule, $start, $end);
        }

        return $created;
    }

    public function generateForRule(RecurringRule $rule, Carbon $start, Carbon $end): int
    {
        if (! $rule->is_active) {
            return 0;
        }

        $created = 0;
        $occurrences = $this->buildOccurrences($rule, $start, $end);

        foreach ($occurrences as $date) {
            if ($rule->end_date && $date->gt($rule->end_date)) {
                continue;
            }

            if ($rule->occurrences_remaining !== null && $rule->occurrences_remaining <= 0) {
                break;
            }

            $item = ScheduledItem::firstOrCreate([
                'user_id' => $rule->user_id,
                'recurring_rule_id' => $rule->id,
                'date' => $date->toDateString(),
                'amount' => $rule->amount,
                'kind' => $rule->kind,
            ], [
                'currency' => $rule->currency,
                'account_id' => $rule->account_id,
                'source_account_id' => $rule->source_account_id,
                'target_account_id' => $rule->target_account_id,
                'category_id' => $rule->category_id,
                'status' => 'pending',
            ]);

            if ($item->wasRecentlyCreated) {
                $created++;
                if ($rule->occurrences_remaining !== null) {
                    $rule->occurrences_remaining = max(0, $rule->occurrences_remaining - 1);
                }
            }
        }

        $nextDate = $this->nextOccurrenceAfter($rule, $end);

        $rule->next_run_on = $nextDate ?? $rule->next_run_on;
        $rule->save();

        return $created;
    }

    protected function buildOccurrences(RecurringRule $rule, Carbon $start, Carbon $end): Collection
    {
        $first = collect([
            Carbon::parse($rule->start_date),
            Carbon::parse($rule->next_run_on),
            $start,
        ])->max();

        return match ($rule->frequency) {
            'weekly' => $this->generateWeekly($rule, $first, $end, 7 * $rule->interval),
            'biweekly' => $this->generateWeekly($rule, $first, $end, 14 * $rule->interval),
            'monthly' => $this->generateMonthly($rule, $first, $end),
            'semimonthly' => $this->generateSemimonthly($rule, $first, $end),
            default => collect(),
        };
    }

    protected function generateWeekly(RecurringRule $rule, Carbon $start, Carbon $end, int $stepDays): Collection
    {
        $current = Carbon::parse($rule->next_run_on);
        while ($current->lt($start)) {
            $current->addDays($stepDays);
        }

        $dates = collect();
        while ($current->lte($end)) {
            if ($current->gte($rule->start_date)) {
                $dates->push($current->copy());
            }
            $current->addDays($stepDays);
        }

        return $dates;
    }

    protected function generateMonthly(RecurringRule $rule, Carbon $start, Carbon $end): Collection
    {
        $dates = collect();
        $desiredDay = $rule->monthly_day ?? Carbon::parse($rule->start_date)->day;
        $baseStart = Carbon::parse($rule->start_date)->startOfMonth();
        $currentMonth = $start->copy()->startOfMonth();

        while ($currentMonth->lte($end)) {
            $diff = $baseStart->diffInMonths($currentMonth);
            if ($diff % $rule->interval === 0) {
                $day = min($desiredDay, $currentMonth->daysInMonth);
                $occurrence = $currentMonth->copy()->day($day);
                if ($occurrence->between($start, $end) && $occurrence->gte($rule->start_date)) {
                    $dates->push($occurrence);
                }
            }
            $currentMonth->addMonth();
        }

        return $dates;
    }

    protected function generateSemimonthly(RecurringRule $rule, Carbon $start, Carbon $end): Collection
    {
        $dates = collect();
        $days = collect([$rule->semimonthly_day_1, $rule->semimonthly_day_2])
            ->filter()
            ->unique()
            ->sort();

        if ($days->isEmpty()) {
            return $dates;
        }

        $baseStart = Carbon::parse($rule->start_date)->startOfMonth();
        $currentMonth = $start->copy()->startOfMonth();

        while ($currentMonth->lte($end)) {
            $diff = $baseStart->diffInMonths($currentMonth);
            if ($diff % $rule->interval === 0) {
                foreach ($days as $day) {
                    $occurrence = $currentMonth->copy()->day(min($day, $currentMonth->daysInMonth));
                    if ($occurrence->between($start, $end) && $occurrence->gte($rule->start_date)) {
                        $dates->push($occurrence);
                    }
                }
            }
            $currentMonth->addMonth();
        }

        return $dates->sort();
    }

    protected function nextOccurrenceAfter(RecurringRule $rule, Carbon $after): ?string
    {
        if ($rule->occurrences_remaining !== null && $rule->occurrences_remaining <= 0) {
            return null;
        }

        $date = match ($rule->frequency) {
            'weekly' => $this->advanceWeeklyAfter($rule, $after, 7 * $rule->interval),
            'biweekly' => $this->advanceWeeklyAfter($rule, $after, 14 * $rule->interval),
            'monthly' => $this->alignMonthlyNext($rule, $after),
            'semimonthly' => $this->nextSemimonthlyDate($rule, $after),
            default => null,
        };

        return $this->respectEndConditions($rule, $date);
    }

    protected function advanceWeeklyAfter(RecurringRule $rule, Carbon $after, int $stepDays): string
    {
        $current = Carbon::parse($rule->next_run_on);
        while ($current->lte($after)) {
            $current->addDays($stepDays);
        }

        return $current->toDateString();
    }

    protected function alignMonthlyNext(RecurringRule $rule, Carbon $after): string
    {
        $desiredDay = $rule->monthly_day ?? Carbon::parse($rule->start_date)->day;
        $candidate = $after->copy()->addDay()->startOfMonth();
        $baseStart = Carbon::parse($rule->start_date)->startOfMonth();

        while (true) {
            $diff = $baseStart->diffInMonths($candidate);
            if ($diff % $rule->interval === 0) {
                $day = min($desiredDay, $candidate->daysInMonth);
                $occurrence = $candidate->copy()->day($day);
                if ($occurrence->gt($after)) {
                    return $occurrence->toDateString();
                }
            }
            $candidate->addMonth();
        }
    }

    protected function nextSemimonthlyDate(RecurringRule $rule, Carbon $after): ?string
    {
        $days = collect([$rule->semimonthly_day_1, $rule->semimonthly_day_2])
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if ($days->isEmpty()) {
            return null;
        }

        $current = $after->copy()->addDay();
        $baseStart = Carbon::parse($rule->start_date)->startOfMonth();

        while (true) {
            $monthStart = $current->copy()->startOfMonth();
            $diff = $baseStart->diffInMonths($monthStart);
            if ($diff % $rule->interval === 0) {
                foreach ($days as $day) {
                    $occurrence = $monthStart->copy()->day(min($day, $monthStart->daysInMonth));
                    if ($occurrence->gte($current)) {
                        return $occurrence->toDateString();
                    }
                }
            }
            $current->addMonth()->startOfMonth();
        }
    }

    protected function respectEndConditions(RecurringRule $rule, ?string $candidate): ?string
    {
        if (! $candidate) {
            return null;
        }

        $candidateDate = Carbon::parse($candidate);

        if ($rule->end_date && $candidateDate->gt($rule->end_date)) {
            return null;
        }

        if ($rule->occurrences_remaining !== null && $rule->occurrences_remaining <= 0) {
            return null;
        }

        return $candidate;
    }
}
