<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('users')
        );
    }

    public function test_permission_tables_exist(): void
    {
        $tables = [
            'roles',
            'permissions',
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table [{$table}] does not exist."
            );
        }
    }

    public function test_database_connection_is_safe(): void
    {
        $database = config('database.connections.mysql.database');

        $this->assertSame(
            'dynamic_cms_testing',
            $database,
        );
    }
}
