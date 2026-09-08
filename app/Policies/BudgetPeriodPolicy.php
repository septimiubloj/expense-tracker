<?php

namespace App\Policies;

use App\Models\BudgetPeriod;
use App\Models\User;

class BudgetPeriodPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BudgetPeriod $budgetPeriod): bool
    {
        return $budgetPeriod->book()->whereBelongsTo($user)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BudgetPeriod $budgetPeriod): bool
    {
        return $this->view($user, $budgetPeriod);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BudgetPeriod $budgetPeriod): bool
    {
        return $this->view($user, $budgetPeriod);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BudgetPeriod $budgetPeriod): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BudgetPeriod $budgetPeriod): bool
    {
        return false;
    }
}
