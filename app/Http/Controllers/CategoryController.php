<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::where('user_id', $request->user()->id)->orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', 'in:income,expense,transfer'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $data['user_id'] = $request->user()->id;

        Category::create($data);

        return redirect()->route('categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        $this->authorizeCategory($category);

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeCategory($category);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', 'in:income,expense,transfer'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $category->update($data);

        return redirect()->route('categories.index')->with('status', 'Category updated.');
    }

    protected function authorizeCategory(Category $category): void
    {
        abort_if($category->user_id !== auth()->id(), 403);
    }
}
