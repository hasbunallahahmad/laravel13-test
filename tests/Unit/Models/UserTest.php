<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_has_soft_deletes_trait(): void
    {
        $traits = class_uses_recursive(User::class);

        $this->assertContains(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            $traits,
        );
    }

    public function test_user_has_roles_trait(): void
    {
        $traits = class_uses_recursive(User::class);

        $this->assertContains(
            \Spatie\Permission\Traits\HasRoles::class,
            $traits,
        );
    }

    public function test_user_initials_are_generated_correctly(): void
    {
        $user = new User([
            'name' => 'Super Administrator',
        ]);

        $this->assertSame(
            'SA',
            $user->initials(),
        );
    }

    public function test_single_name_generates_single_initial(): void
    {
        $user = new User([
            'name' => 'Hasbi',
        ]);

        $this->assertSame(
            'H',
            $user->initials(),
        );
    }
}
