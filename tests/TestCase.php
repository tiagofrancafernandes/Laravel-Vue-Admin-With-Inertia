<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\AdminUserSeeder;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected static int $seederInitialUsersCount = 0;
    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cached roles and permissions before each test
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Count seeder users
        static::$seederInitialUsersCount = count(AdminUserSeeder::initialUsers());
    }

    protected function tearDown(): void
    {
        // Reset cached roles and permissions after each test
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        parent::tearDown();
    }
}
