@extends('layouts.app')

@section('title', 'Import Preview')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1">Preview Import</h1>
        <p class="text-muted mb-0">Select which rows to import into {{ $account->name }}.</p>
    </div>
    <a href="{{ route('import.index') }}" class="btn btn-outline-secondary">Start Over</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <form method="POST" action="{{ route('import.commit') }}">
            @csrf
            <input type="hidden" name="file_path" value="{{ $filePath }}">
            <input type="hidden" name="account_id" value="{{ $account->id }}">
            <input type="hidden" name="funding_account_id" value="{{ $funding?->id }}">
            <input type="hidden" name="source" value="{{ $source }}">

            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 6%">
                                <input type="checkbox" class="form-check-input" id="select_all">
                            </th>
                            <th style="width: 12%">Date</th>
                            <th>Description</th>
                            <th style="width: 12%" class="text-end">Amount</th>
                            <th style="width: 14%">Type</th>
                            <th style="width: 16%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            @php
                                $checked = $row['duplicate'] !== 'exact' && !$row['locked'];
                                $typeLabel = str_replace('_', ' ', $row['data']['type']);
                            @endphp
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input row-check" name="selected[]" value="{{ $row['index'] }}" @checked($checked) @disabled($row['locked'])>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($row['data']['date'])->toFormattedDateString() }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $row['row']['description'] ?: '—' }}</div>
                                    <div class="text-muted small">Import source: {{ $source }}</div>
                                </td>
                                <td class="text-end fw-semibold">${{ number_format($row['data']['amount'], 2) }}</td>
                                <td><span class="badge bg-secondary text-uppercase">{{ $typeLabel }}</span></td>
                                <td>
                                    @if ($row['locked'])
                                        <span class="badge bg-danger">Locked Month</span>
                                    @elseif ($row['duplicate'] === 'exact')
                                        <span class="badge bg-danger">Exact Duplicate</span>
                                    @elseif ($row['duplicate'] === 'near')
                                        <span class="badge bg-warning text-dark">Near Duplicate</span>
                                    @else
                                        <span class="badge bg-success">New</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">Exact duplicates and locked months are unchecked by default to avoid conflicts.</div>
                <button class="btn btn-primary" type="submit">Import Selected</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const selectAll = document.getElementById('select_all');
    const checkboxes = document.querySelectorAll('.row-check');
    if (selectAll && checkboxes.length) {
        selectAll.addEventListener('change', (event) => {
            checkboxes.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = event.target.checked;
                }
            });
        });
    }
</script>
@endpush
