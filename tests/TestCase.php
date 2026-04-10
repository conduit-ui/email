<?php

namespace ConduitUI\Email\Tests;

use ConduitUI\Email\EmailServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\LaravelData\LaravelDataServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            EmailServiceProvider::class,
        ];
    }
}
