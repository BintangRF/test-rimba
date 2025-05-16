<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'staff']);
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }
}
