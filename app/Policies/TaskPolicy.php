<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    public function delete(User $user, Task $task)
    {
        return $user->id === $task->created_by || $user->role === 'admin'
            ? Response::allow()
            : Response::deny('You do not own this task.');
    }
}