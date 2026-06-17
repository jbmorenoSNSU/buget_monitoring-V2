<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function all(): Collection
    {
        return Category::orderBy('name')->get(['id', 'name', 'type', 'icon', 'color', 'is_active']);
    }

    public function all_active(): Collection
    {
        return Category::active()->orderBy('name')->get(['id', 'name', 'type']);
    }

    public function all_active_expense(): Collection
    {
        return Category::active()->expense()->orderBy('name')->get(['id', 'name', 'icon', 'color']);
    }

    public function find(int $id): ?Category
    {
        return Category::find($id);
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(Category $category): bool
    {
        return (bool) $category->delete();
    }

    public function has_transactions(Category $category): bool
    {
        return $category->transactions()->exists();
    }
}
