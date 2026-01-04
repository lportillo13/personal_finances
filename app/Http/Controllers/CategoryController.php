<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['income', 'expense', 'transfer'])],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        Category::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'color' => $validated['color'] ?? null,
        ]);

        return Redirect::route('categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        $this->authorizeCategory($category);

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeCategory($category);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['income', 'expense', 'transfer'])],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $category->update($validated);

        return Redirect::route('categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorizeCategory($category);
        $category->delete();

        return Redirect::route('categories.index')->with('status', 'Category deleted.');
    }

    protected function authorizeCategory(Category $category): void
    {
        abort_if($category->user_id !== auth()->id(), 403);
    }
}
