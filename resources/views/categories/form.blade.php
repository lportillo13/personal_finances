@csrf
<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Kind</label>
    <select name="kind" class="form-select" required>
        @foreach (['income' => 'Income', 'expense' => 'Expense', 'transfer' => 'Transfer'] as $value => $label)
            <option value="{{ $value }}" @selected(old('kind', $category->kind ?? '') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Color</label>
    <input type="text" name="color" class="form-control" value="{{ old('color', $category->color ?? '') }}" placeholder="#22c55e">
</div>
<button class="btn btn-primary" type="submit">Save</button>
<a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
