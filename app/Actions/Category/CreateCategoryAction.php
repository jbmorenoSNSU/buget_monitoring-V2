<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\DTOs\CategoryDTO;
use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;

/**
 * Single-purpose action for creating a category.
 */
class CreateCategoryAction
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Execute the category creation.
     */
    public function execute(CategoryDTO $dto): Category
    {
        return $this->categoryRepository->create($dto->toArray());
    }
}
