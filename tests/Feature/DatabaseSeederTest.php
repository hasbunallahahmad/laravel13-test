<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_permission_seeder_creates_required_roles(): void
    {
        $this->seed(RolePermissionSeeder::class);

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
        $this->seed(RolePermissionSeeder::class);

        $this->seed(SuperAdminSeeder::class);

        $user = User::query()
            ->where('email', 'admin@dynamiccms.test')
            ->first();

        $this->assertNotNull($user);

        $this->assertTrue($user->is_active);

        $this->assertTrue(
            $user->hasRole('super-admin')
        );
    }

    public function test_role_permission_seeder_creates_required_menu_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $permissions = [
            'menus.view',
            'menus.create',
            'menus.update',
            'menus.delete',
            'menus.restore',
            'menus.force-delete',
        ];

        foreach ($permissions as $permission) {
            $this->assertDatabaseHas('permissions', [
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }

    public function test_admin_role_has_required_menu_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = Role::query()
            ->where('name', 'admin')
            ->firstOrFail();

        $this->assertTrue($admin->hasPermissionTo('menus.view'));
        $this->assertTrue($admin->hasPermissionTo('menus.create'));
        $this->assertTrue($admin->hasPermissionTo('menus.update'));
        $this->assertTrue($admin->hasPermissionTo('menus.delete'));
        $this->assertTrue($admin->hasPermissionTo('menus.restore'));

        $this->assertFalse($admin->hasPermissionTo('menus.force-delete'));
    }

    public function test_super_admin_role_has_all_menu_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $superAdmin = Role::query()
            ->where('name', 'super-admin')
            ->firstOrFail();

        $permissions = [
            'menus.view',
            'menus.create',
            'menus.update',
            'menus.delete',
            'menus.restore',
            'menus.force-delete',
        ];

        foreach ($permissions as $permission) {
            $this->assertTrue(
                $superAdmin->hasPermissionTo($permission),
                "Super admin does not have permission [{$permission}]."
            );
        }
    }

    public function test_role_permission_seeder_creates_required_homepage_cta_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $permissions = [
            'homepage-ctas.view',
            'homepage-ctas.create',
            'homepage-ctas.update',
            'homepage-ctas.delete',
            'homepage-ctas.restore',
            'homepage-ctas.force-delete',
        ];

        foreach ($permissions as $permission) {
            $this->assertDatabaseHas('permissions', [
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }

    public function test_admin_role_has_all_homepage_cta_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = Role::query()
            ->where('name', 'admin')
            ->firstOrFail();

        $permissions = [
            'homepage-ctas.view',
            'homepage-ctas.create',
            'homepage-ctas.update',
            'homepage-ctas.delete',
            'homepage-ctas.restore',
            'homepage-ctas.force-delete',
        ];

        foreach ($permissions as $permission) {
            $this->assertTrue(
                $admin->hasPermissionTo($permission),
                "Admin does not have permission [{$permission}]."
            );
        }
    }

    public function test_super_admin_role_has_all_homepage_cta_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $superAdmin = Role::query()
            ->where('name', 'super-admin')
            ->firstOrFail();

        $permissions = [
            'homepage-ctas.view',
            'homepage-ctas.create',
            'homepage-ctas.update',
            'homepage-ctas.delete',
            'homepage-ctas.restore',
            'homepage-ctas.force-delete',
        ];

        foreach ($permissions as $permission) {
            $this->assertTrue(
                $superAdmin->hasPermissionTo($permission),
                "Super admin does not have permission [{$permission}]."
            );
        }
    }

    public function test_other_roles_do_not_have_homepage_cta_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $roles = [
            'editor',
            'author',
            'contributor',
            'viewer',
        ];

        $permissions = [
            'homepage-ctas.view',
            'homepage-ctas.create',
            'homepage-ctas.update',
            'homepage-ctas.delete',
            'homepage-ctas.restore',
            'homepage-ctas.force-delete',
        ];

        foreach ($roles as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->firstOrFail();

            foreach ($permissions as $permission) {
                $this->assertFalse(
                    $role->hasPermissionTo($permission),
                    "Role [{$roleName}] unexpectedly has permission [{$permission}]."
                );
            }
        }
    }

    public function test_role_permission_seeder_creates_users_restore_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->assertDatabaseHas('permissions', [
            'name' => 'users.restore',
            'guard_name' => 'web',
        ]);
    }

    public function test_admin_role_has_users_restore_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = Role::query()
            ->where('name', 'admin')
            ->firstOrFail();

        $this->assertTrue($admin->hasPermissionTo('users.restore'));
    }

    public function test_super_admin_role_has_users_restore_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $superAdmin = Role::query()
            ->where('name', 'super-admin')
            ->firstOrFail();

        $this->assertTrue($superAdmin->hasPermissionTo('users.restore'));
    }

    public function test_other_roles_do_not_have_users_restore_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $roles = [
            'editor',
            'author',
            'contributor',
            'viewer',
        ];

        foreach ($roles as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->firstOrFail();

            $this->assertFalse(
                $role->hasPermissionTo('users.restore'),
                "Role [{$roleName}] unexpectedly has permission [users.restore]."
            );
        }
    }
}
