<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service class handling business logic for category management.
 */
class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Get all categories.
     */
    public function get_all(): Collection
    {
        return $this->categoryRepository->all();
    }

    /**
     * Toggle the active status of a category.
     */
    public function toggle(Category $category): Category
    {
        return $this->categoryRepository->update($category, ['is_active' => ! $category->is_active]);
    }

    /**
     * Determine if a category can be safely deleted.
     */
    public function can_delete(Category $category): bool
    {
        return ! $this->categoryRepository->has_transactions($category);
    }

    /**
     * Delete a category.
     */
    public function delete(Category $category): bool
    {
        return $this->categoryRepository->delete($category);
    }
}
