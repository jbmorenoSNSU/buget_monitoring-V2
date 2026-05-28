<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\DTOs\CategoryDTO;
use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;

/**
 * Single-purpose action for updating a category.
 */
class UpdateCategoryAction
{
    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Execute the category update.
     *
     * @param Category $category
     * @param CategoryDTO $dto
     * @return Category
     */
    public function execute(Category $category, CategoryDTO $dto): Category
    {
        return $this->categoryRepository->update($category, $dto->toArray());
    }
}
