<?php

namespace ConduitUI\Email\Drivers\Gmail\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetMessage extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $messageId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/users/me/messages/'.$this->messageId;
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        return [
            'format' => 'metadata',
            'metadataHeaders' => 'Subject,From,Date',
        ];
    }
}
