<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_permission_seeder_creates_required_roles(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $roles = [
            'super-admin',
            'admin',
            'editor',
            'author',
        ];

        foreach ($roles as $role) {
            $this->assertTrue(
                Role::query()
                    ->where('name', $role)
                    ->exists(),
                "Role [{$role}] was not created."
            );
        }
    }

    public function test_super_admin_seeder_creates_active_user_with_role(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->seed(\Database\Seeders\SuperAdminSeeder::class);

        $user = User::query()
            ->where('email', 'admin@dynamiccms.test')
            ->first();

        $this->assertNotNull($user);

        $this->assertTrue($user->is_active);

        $this->assertTrue(
            $user->hasRole('super-admin')
        );
    }
}
