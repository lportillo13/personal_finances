@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">Name</label>
        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name ?? '') }}" required>
        @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="kind">Kind</label>
        <select id="kind" name="kind" class="form-select @error('kind') is-invalid @enderror" required>
            @foreach (['income' => 'Income', 'expense' => 'Expense', 'transfer' => 'Transfer'] as $value => $label)
                <option value="{{ $value }}" @selected(old('kind', $category->kind ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('kind')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-md-6">
        <label class="form-label" for="color">Color</label>
        <input type="text" id="color" name="color" class="form-control @error('color') is-invalid @enderror" value="{{ old('color', $category->color ?? '') }}" placeholder="#22c55e">
        @error('color')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="d-flex gap-2 mt-3">
    <button class="btn btn-primary" type="submit">Save</button>
    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
