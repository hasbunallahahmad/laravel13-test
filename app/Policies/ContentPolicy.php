<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Content;
use App\Models\User;

class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('content.view');
    }

    public function view(User $user, Content $content): bool
    {
        return $user->can('content.view');
    }

    public function create(User $user): bool
    {
        return $user->can('content.create');
    }

    public function update(User $user, Content $content): bool
    {
        return $user->can('content.update');
    }

    public function delete(User $user, Content $content): bool
    {
        return $user->can('content.delete');
    }

    public function restore(User $user, Content $content): bool
    {
        return $user->can('content.restore');
    }

    public function forceDelete(User $user, Content $content): bool
    {
        return $user->can('content.force-delete');
    }

    public function publish(User $user, Content $content): bool
    {
        return $user->can('content.publish');
    }
}
