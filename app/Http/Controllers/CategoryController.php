<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Category\CreateCategoryAction;
use App\Actions\Category\UpdateCategoryAction;
use App\DTOs\CategoryDTO;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles HTTP actions for category management.
 */
class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $service,
        private CreateCategoryAction $createCategory,
        private UpdateCategoryAction $updateCategory,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Categories/Index', [
            'categories' => CategoryResource::collection($this->service->get_all()),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);
        $this->createCategory->execute(CategoryDTO::fromArray($request->validated()));

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $this->updateCategory->execute($category, CategoryDTO::fromArray($request->validated()));

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);
        if (! $this->service->can_delete($category)) {
            return redirect()->route('categories.index')->with('error', 'Cannot delete category with transactions. Deactivate it instead.');
        }
        $this->service->delete($category);

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    public function toggle(Category $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $this->service->toggle($category);
        $status = $category->fresh()->is_active ? 'activated' : 'deactivated';

        return redirect()->route('categories.index')->with('success', "Category {$status} successfully.");
    }
}
