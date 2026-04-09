<?php

namespace ConduitUI\Email\Drivers\Gmail\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchMessages extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $searchQuery,
        protected int $maxResults = 10,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/users/me/messages';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return [
            'q' => $this->searchQuery,
            'maxResults' => $this->maxResults,
        ];
    }
}
