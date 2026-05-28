<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/**
 * Class CategoryPolicy
 *
 * Handles authorization checks for Category model access.
 */
class CategoryPolicy
{
    /**
     * Determine if any categories can be viewed.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a specific category can be viewed.
     */
    public function view(?User $user, Category $category): bool
    {
        return true;
    }

    /**
     * Determine if a category can be created.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a category can be updated.
     */
    public function update(?User $user, Category $category): bool
    {
        return true;
    }

    /**
     * Determine if a category can be deleted.
     */
    public function delete(?User $user, Category $category): bool
    {
        return true;
    }
}
