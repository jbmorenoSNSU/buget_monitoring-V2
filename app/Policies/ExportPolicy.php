<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Export;
use App\Models\User;

/**
 * Handles authorization checks for Export model access.
 */
class ExportPolicy
{
    /**
     * Determine if any exports can be viewed.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a specific export can be viewed.
     */
    public function view(?User $user, Export $export): bool
    {
        return true;
    }

    /**
     * Determine if an export can be created.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if an export can be downloaded.
     */
    public function download(?User $user, Export $export): bool
    {
        return true;
    }

    /**
     * Determine if an export can be deleted.
     */
    public function delete(?User $user, Export $export): bool
    {
        return true;
    }
}
