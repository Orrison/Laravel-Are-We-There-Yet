<?php

namespace Orrison\AreWeThereYet\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orrison\AreWeThereYet\Providers\AreWeThereYetServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(realpath(__DIR__ . '/../src/Migrations'));
    }

    protected function getPackageProviders($app)
    {
        return [
            AreWeThereYetServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}
