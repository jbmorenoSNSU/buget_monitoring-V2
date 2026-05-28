<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Person;
use App\Models\User;

/**
 * Class PersonPolicy
 *
 * Handles authorization checks for Person model access.
 */
class PersonPolicy
{
    /**
     * Determine if any persons can be viewed.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a specific person can be viewed.
     */
    public function view(?User $user, Person $person): bool
    {
        return true;
    }

    /**
     * Determine if a person can be created.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determine if a person can be updated.
     */
    public function update(?User $user, Person $person): bool
    {
        return true;
    }

    /**
     * Determine if a person can be deleted.
     */
    public function delete(?User $user, Person $person): bool
    {
        return true;
    }
}
