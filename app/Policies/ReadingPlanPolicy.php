<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ReadingPlan;
use App\Enums\ReadingPlanStatus;

class ReadingPlanPolicy
{
    public function update(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id
            && $readingPlan->status !== ReadingPlanStatus::Completed;
    }

    public function delete(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }
}
