<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TestingEnvironmentTest extends TestCase
{
    public function test_application_uses_testing_environment(): void
    {
        $this->assertTrue(app()->environment('testing'));
    }

    public function test_application_uses_testing_database(): void
    {
        $database = DB::connection()->getDatabaseName();

        $this->assertSame(
            'dynamic_cms_testing',
            $database,
        );
    }

    public function test_development_database_is_never_used(): void
    {
        $database = DB::connection()->getDatabaseName();

        $this->assertNotSame(
            'dynamic_cms',
            $database,
        );
    }
}
