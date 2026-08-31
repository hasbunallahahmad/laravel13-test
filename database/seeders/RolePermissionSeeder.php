<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [

            // Dashboard
            'dashboard.view',

            // Users
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Roles
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',

            // Permissions
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',

            // Settings
            'settings.view',
            'settings.update',

            // Content
            'content.view',
            'content.create',
            'content.update',
            'content.delete',
            'content.publish',

            // Media
            'media.view',
            'media.upload',
            'media.update',
            'media.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate(
                $permission,
                'web',
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::findOrCreate(
            'super-admin',
            'web',
        );

        $admin = Role::findOrCreate(
            'admin',
            'web',
        );

        $editor = Role::findOrCreate(
            'editor',
            'web',
        );

        $author = Role::findOrCreate(
            'author',
            'web',
        );

        $contributor = Role::findOrCreate(
            'contributor',
            'web',
        );

        $viewer = Role::findOrCreate(
            'viewer',
            'web',
        );

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        $superAdmin->syncPermissions(
            Permission::all(),
        );

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */

        $admin->syncPermissions([
            'dashboard.view',

            'users.view',
            'users.create',
            'users.update',

            'content.view',
            'content.create',
            'content.update',
            'content.delete',
            'content.publish',

            'media.view',
            'media.upload',
            'media.update',
            'media.delete',

            'settings.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Editor
        |--------------------------------------------------------------------------
        */

        $editor->syncPermissions([
            'dashboard.view',

            'content.view',
            'content.create',
            'content.update',
            'content.delete',
            'content.publish',

            'media.view',
            'media.upload',
            'media.update',
            'media.delete',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Author
        |--------------------------------------------------------------------------
        */

        $author->syncPermissions([
            'dashboard.view',

            'content.view',
            'content.create',
            'content.update',

            'media.view',
            'media.upload',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Contributor
        |--------------------------------------------------------------------------
        */

        $contributor->syncPermissions([
            'dashboard.view',

            'content.view',
            'content.create',

            'media.view',
            'media.upload',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Viewer
        |--------------------------------------------------------------------------
        */

        $viewer->syncPermissions([
            'dashboard.view',
            'content.view',
            'media.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Clear Cache
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
