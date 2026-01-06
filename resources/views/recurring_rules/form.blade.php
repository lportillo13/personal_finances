@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">Name</label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $rule->name ?? '') }}" required>
        @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="kind">Kind</label>
        <select name="kind" id="kind" class="form-select @error('kind') is-invalid @enderror" required>
            @foreach (['income' => 'Income', 'expense' => 'Expense', 'transfer' => 'Transfer'] as $value => $label)
                <option value="{{ $value }}" @selected(old('kind', $rule->kind ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('kind')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="amount">Amount</label>
        <input type="number" step="0.01" id="amount" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $rule->amount ?? '') }}" required>
        @error('amount')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-3">
        <label class="form-label" for="currency">Currency</label>
        <input type="text" id="currency" name="currency" class="form-control @error('currency') is-invalid @enderror" value="{{ old('currency', $rule->currency ?? 'USD') }}" maxlength="3">
        @error('currency')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="account_id">Account</label>
        <select name="account_id" id="account_id" class="form-select @error('account_id') is-invalid @enderror">
            <option value="">Select</option>
            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('account_id', $rule->account_id ?? '') == $account->id)>{{ $account->name }}</option>
            @endforeach
        </select>
        @error('account_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="source_account_id">Source Account</label>
        <select name="source_account_id" id="source_account_id" class="form-select @error('source_account_id') is-invalid @enderror">
            <option value="">Select</option>
            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('source_account_id', $rule->source_account_id ?? '') == $account->id)>{{ $account->name }}</option>
            @endforeach
        </select>
        @error('source_account_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="target_account_id">Target Account</label>
        <select name="target_account_id" id="target_account_id" class="form-select @error('target_account_id') is-invalid @enderror">
            <option value="">Select</option>
            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" @selected(old('target_account_id', $rule->target_account_id ?? '') == $account->id)>{{ $account->name }}</option>
            @endforeach
        </select>
        @error('target_account_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-4">
        <label class="form-label" for="category_id">Category</label>
        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror">
            <option value="">Select</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $rule->category_id ?? '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="frequency">Frequency</label>
        <select name="frequency" id="frequency" class="form-select @error('frequency') is-invalid @enderror" required>
            @foreach (['weekly' => 'Weekly', 'biweekly' => 'Biweekly', 'semimonthly' => 'Semimonthly', 'monthly' => 'Monthly'] as $value => $label)
                <option value="{{ $value }}" @selected(old('frequency', $rule->frequency ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('frequency')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="interval">Interval</label>
        <input type="number" name="interval" id="interval" min="1" class="form-control @error('interval') is-invalid @enderror" value="{{ old('interval', $rule->interval ?? 1) }}">
        @error('interval')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row g-3 mt-1" id="monthly-day-group">
    <div class="col-md-4">
        <label class="form-label" for="monthly_day">Monthly Day</label>
        <input type="number" name="monthly_day" id="monthly_day" min="1" max="31" class="form-control @error('monthly_day') is-invalid @enderror" value="{{ old('monthly_day', $rule->monthly_day ?? '') }}">
        @error('monthly_day')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row g-3 mt-1" id="semimonthly-day-group">
    <div class="col-md-4">
        <label class="form-label" for="semimonthly_day_1">Semimonthly Day 1</label>
        <input type="number" name="semimonthly_day_1" id="semimonthly_day_1" min="1" max="31" class="form-control @error('semimonthly_day_1') is-invalid @enderror" value="{{ old('semimonthly_day_1', $rule->semimonthly_day_1 ?? '') }}">
        @error('semimonthly_day_1')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="semimonthly_day_2">Semimonthly Day 2</label>
        <input type="number" name="semimonthly_day_2" id="semimonthly_day_2" min="1" max="31" class="form-control @error('semimonthly_day_2') is-invalid @enderror" value="{{ old('semimonthly_day_2', $rule->semimonthly_day_2 ?? '') }}">
        @error('semimonthly_day_2')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-4">
        <label class="form-label" for="start_date">Start Date</label>
        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', optional($rule->start_date)->toDateString()) }}" required>
        @error('start_date')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="end_date">End Date</label>
        <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', optional($rule->end_date)->toDateString()) }}">
        @error('end_date')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="next_run_on">Next Run On</label>
        <input type="date" name="next_run_on" id="next_run_on" class="form-control @error('next_run_on') is-invalid @enderror" value="{{ old('next_run_on', optional($rule->next_run_on)->toDateString()) }}">
        @error('next_run_on')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-6">
        <label class="form-label" for="occurrences_total">Occurrences Total</label>
        <input type="number" name="occurrences_total" id="occurrences_total" min="1" class="form-control @error('occurrences_total') is-invalid @enderror" value="{{ old('occurrences_total', $rule->occurrences_total ?? '') }}">
        @error('occurrences_total')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="occurrences_remaining">Occurrences Remaining</label>
        <input type="number" name="occurrences_remaining" id="occurrences_remaining" min="1" class="form-control @error('occurrences_remaining') is-invalid @enderror" value="{{ old('occurrences_remaining', $rule->occurrences_remaining ?? '') }}">
        @error('occurrences_remaining')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="form-check mt-3">
    <input type="checkbox" name="is_active" class="form-check-input @error('is_active') is-invalid @enderror" id="is_active" value="1" @checked(old('is_active', $rule->is_active ?? true))>
    <label for="is_active" class="form-check-label">Active</label>
    @error('is_active')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
<div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary" type="submit">Save</button>
    <a href="{{ route('recurring-rules.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const frequency = document.getElementById('frequency');
        const monthlyGroup = document.getElementById('monthly-day-group');
        const semimonthlyGroup = document.getElementById('semimonthly-day-group');

        function toggleFrequencyFields() {
            const value = frequency.value;
            monthlyGroup.classList.toggle('d-none', value !== 'monthly');
            semimonthlyGroup.classList.toggle('d-none', value !== 'semimonthly');
        }

        frequency.addEventListener('change', toggleFrequencyFields);
        toggleFrequencyFields();
    });
</script>
@endpush
