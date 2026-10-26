<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPolicy extends CmsPolicy
{
    protected string $area = 'users';

    public function delete(User $user, Model $record): bool
    {
        return $record instanceof User && $record->canBeDeletedBy($user);
    }
}
