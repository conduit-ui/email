<?php

namespace ConduitUI\Email;

use Illuminate\Support\ServiceProvider;

class EmailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/email-client.php', 'email-client');

        $this->app->singleton(EmailManager::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/email-client.php' => config_path('email-client.php'),
            ], 'email-config');
        }
    }
}
