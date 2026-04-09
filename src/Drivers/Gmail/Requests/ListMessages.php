<?php

namespace ConduitUI\Email\Drivers\Gmail\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListMessages extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $searchQuery = '',
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
        $query = [
            'maxResults' => $this->maxResults,
        ];

        if ($this->searchQuery !== '') {
            $query['q'] = $this->searchQuery;
        }

        return $query;
    }
}
