<?php

namespace ConduitUI\Email;

use ConduitUI\Email\Contracts\EmailProvider;
use ConduitUI\Email\Drivers\Gmail\GmailDriver;

class EmailManager
{
    /** @var array<string, EmailProvider> */
    private array $drivers = [];

    public function driver(?string $name = null): EmailProvider
    {
        $name ??= config('email-client.default', 'gmail');

        return $this->drivers[$name] ??= $this->resolve($name);
    }

    private function resolve(string $name): EmailProvider
    {
        $config = config("email-client.drivers.{$name}");

        return match ($name) {
            'gmail' => new GmailDriver($config['access_token'] ?? ''),
            default => throw new \InvalidArgumentException("Email driver [{$name}] is not supported."),
        };
    }
}
