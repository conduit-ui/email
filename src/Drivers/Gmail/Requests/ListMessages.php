<?php

namespace ConduitUI\Email\Drivers\Gmail\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListMessages extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $searchQuery = '',
        private readonly int $maxResults = 10,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/users/me/messages';
    }

    protected function defaultQuery(): array
    {
        $params = ['maxResults' => $this->maxResults];

        if ($this->searchQuery !== '') {
            $params['q'] = $this->searchQuery;
        }

        return $params;
    }
}
