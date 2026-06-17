<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles HTTP actions for category management.
 */
class CategoryController extends Controller
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function index(): Response
    {
        return Inertia::render('Categories/Index', [
            'categories' => CategoryResource::collection($this->repository->all()),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);
        $this->repository->create($request->validated());

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $this->repository->update($category, $request->validated());

        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);
        if ($this->repository->has_transactions($category)) {
            return redirect()->back()->with('error', 'Cannot delete category with transactions. Deactivate it instead.');
        }
        $this->repository->delete($category);

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }

    public function toggle(Category $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $this->repository->update($category, ['is_active' => ! $category->is_active]);
        $status = $category->fresh()->is_active ? 'activated' : 'deactivated';

        return redirect()->back()->with('success', "Category {$status} successfully.");
    }
}
