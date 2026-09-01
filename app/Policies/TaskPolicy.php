<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TaskPolicy extends ResourcePolicy
{
    protected string $prefix = 'tasks';

    /** A task is visible to its assignee, its creator, and anyone who can see all. */
    public function view(User $user, Model $model): bool
    {
        if (! $user->can('tasks.view')) {
            return false;
        }

        return $this->owns($user, $model)
            || ($model instanceof Task && (int) $model->created_by === $user->id);
    }

    public function complete(User $user, Task $task): bool
    {
        return $this->view($user, $task) && $task->status === 'open';
    }
}
