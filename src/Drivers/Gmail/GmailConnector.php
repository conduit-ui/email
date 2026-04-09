<?php

namespace ConduitUI\Email\Drivers\Gmail;

use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;

class GmailConnector extends Connector
{
    public function __construct(private readonly string $accessToken) {}

    public function resolveBaseUrl(): string
    {
        return 'https://gmail.googleapis.com/gmail/v1';
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator($this->accessToken);
    }

    protected function defaultHeaders(): array
    {
        return ['Accept' => 'application/json'];
    }
}
