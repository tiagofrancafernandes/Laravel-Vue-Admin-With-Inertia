<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Database\Seeders\AdminUserSeeder;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    use InteractsWithDatabase;
    use DatabaseTruncation;

    protected static int $seederInitialUsersCount = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->truncateDatabaseTables();

        // Run seeds before each test
        $this->seed();
        static::$seederInitialUsersCount = count(AdminUserSeeder::initialUsers());

        // $this->seed(MySeeder::class);
    }
}
