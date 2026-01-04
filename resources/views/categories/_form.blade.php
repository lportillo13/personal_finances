<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Type</label>
    <select name="type" class="form-select" required>
        @foreach(['income' => 'Income', 'expense' => 'Expense', 'transfer' => 'Transfer'] as $value => $label)
            <option value="{{ $value }}" @selected(old('type', $category->type ?? '') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Color</label>
    <input type="text" name="color" class="form-control" value="{{ old('color', $category->color ?? '') }}" placeholder="#4287f5">
</div>
