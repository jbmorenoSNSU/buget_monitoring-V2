<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Category\CreateCategoryAction;
use App\Actions\Category\UpdateCategoryAction;
use App\DTOs\CategoryDTO;
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
        private CategoryRepositoryInterface $categoryRepository,
        private CreateCategoryAction $createCategory,
        private UpdateCategoryAction $updateCategory,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Categories/Index', [
            'categories' => CategoryResource::collection($this->categoryRepository->all()),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Categories/Form');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);
        $this->createCategory->execute(CategoryDTO::fromArray($request->validated()));
        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('Categories/Form', [
            'category' => new CategoryResource($category),
        ]);
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
        if ($this->categoryRepository->has_transactions($category)) {
            return redirect()->route('categories.index')->with('error', 'Cannot delete category with transactions. Deactivate it instead.');
        }
        $this->categoryRepository->delete($category);
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    public function toggle(Category $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $this->categoryRepository->update($category, ['is_active' => !$category->is_active]);
        $status = $category->fresh()->is_active ? 'activated' : 'deactivated';
        return redirect()->route('categories.index')->with('success', "Category {$status} successfully.");
    }
}
