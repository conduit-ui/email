<?php

namespace ConduitUI\Email\Tests;

use ConduitUI\Email\EmailServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            EmailServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('email-client.default', 'gmail');
        $app['config']->set('email-client.drivers.gmail.access_token', 'test-token');
    }
}
