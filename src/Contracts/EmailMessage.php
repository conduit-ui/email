<?php

namespace ConduitUI\Email\Contracts;

use Spatie\LaravelData\Data;

class EmailMessage extends Data
{
    public function __construct(
        public string $id,
        public string $threadId,
        public string $subject,
        public string $from,
        public ?string $fromName,
        public ?string $date,
        public string $snippet,
        /** @var array<int, string> */
        public array $labelIds,
    ) {}
}
