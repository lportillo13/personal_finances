@extends('layouts.app')

@section('title', 'Month Locks')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1">Month Locks</h1>
        <p class="text-muted mb-0">Prevent changes to completed periods.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 mb-3">Lock Last Month</h2>
                <form method="POST" action="{{ route('locks.lockLast') }}" class="d-flex flex-column gap-2">
                    @csrf
                    <div>
                        <label for="note_last" class="form-label">Note (optional)</label>
                        <input type="text" id="note_last" name="note" class="form-control" maxlength="255" placeholder="Why lock this month?">
                    </div>
                    <button class="btn btn-outline-primary" type="submit">Lock {{ $today->copy()->subMonth()->format('F Y') }}</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 mb-3">Lock Specific Month</h2>
                <form method="POST" action="{{ route('locks.store') }}" class="d-flex flex-column gap-2">
                    @csrf
                    <div>
                        <label for="month" class="form-label">Month</label>
                        <input type="month" name="month" id="month" class="form-control" value="{{ $today->format('Y-m') }}" required>
                    </div>
                    <div>
                        <label for="note" class="form-label">Note (optional)</label>
                        <input type="text" id="note" name="note" class="form-control" maxlength="255" placeholder="Reminder for this lock">
                    </div>
                    <button class="btn btn-outline-primary" type="submit">Lock Month</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        @if ($locks->isEmpty())
            <p class="text-muted text-center my-4 mb-0">No locked months yet.</p>
        @else
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 20%">Month</th>
                            <th>Note</th>
                            <th style="width: 20%">Locked At</th>
                            <th style="width: 10%" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($locks as $lock)
                            <tr>
                                <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $lock->month)->format('F Y') }}</td>
                                <td>{{ $lock->note ?? '—' }}</td>
                                <td>{{ optional($lock->locked_at)->toDayDateTimeString() }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('locks.destroy', $lock) }}" onsubmit="return confirm('Unlock this month? Changes will be allowed again.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Unlock</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
