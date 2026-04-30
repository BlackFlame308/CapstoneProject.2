<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\User;

class HouseholdPolicy
{
    public function view(User $user): bool
    {
        return $user->canViewHouseholds();
    }

    public function create(User $user): bool
    {
        return $user->canManageHouseholds();
    }

    public function update(User $user, Household $household): bool
    {
        return $user->canManageHouseholds();
    }

    public function delete(User $user, Household $household): bool
    {
        return $user->canManageHouseholds();
    }
}

