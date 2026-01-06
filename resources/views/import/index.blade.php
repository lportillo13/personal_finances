@extends('layouts.app')

@section('title', 'Import Transactions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1">Import Transactions</h1>
        <p class="text-muted mb-0">Upload a CSV from your bank or credit card.</p>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('import.preview') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-12">
                <label for="file" class="form-label">CSV File</label>
                <input class="form-control" type="file" name="file" id="file" accept=".csv,text/csv" required>
                <div class="form-text">Supported formats: Date/Description/Amount or Transaction Date/Post Date/Description/Debit/Credit.</div>
            </div>
            <div class="col-md-6">
                <label for="account_id" class="form-label">Import into Account</label>
                <select name="account_id" id="account_id" class="form-select" required>
                    <option value="">Choose an account</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }} ({{ str_replace('_', ' ', $account->type) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="funding_account_id" class="form-label">Funding Account for Card Payments (optional)</label>
                <select name="funding_account_id" id="funding_account_id" class="form-select">
                    <option value="">None / Adjustment</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }} ({{ str_replace('_', ' ', $account->type) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="source" class="form-label">Source Label</label>
                <input type="text" name="source" id="source" class="form-control" value="csv" maxlength="50" placeholder="e.g. chase_csv">
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit">Preview Import</button>
            </div>
        </form>
    </div>
</div>
@endsection
