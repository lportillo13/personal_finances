<?php

namespace App\Http\Controllers;

use App\Services\ScheduleGenerator;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(private ScheduleGenerator $generator)
    {
    }

    public function generate(Request $request): RedirectResponse
    {
        $start = Carbon::today();
        $end = Carbon::today()->addDays(90);

        $this->generator->generateForUser($request->user(), $start, $end);

        return back()->with('status', 'Schedule generated (next 90 days).');
    }
}
