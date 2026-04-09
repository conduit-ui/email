<?php

namespace ConduitUI\Email\Contracts;

use Illuminate\Support\Collection;

interface EmailProvider
{
    /** @return Collection<int, EmailMessage> */
    public function listMessages(string $query = '', int $maxResults = 10): Collection;

    public function getMessage(string $messageId): EmailMessage;

    /** @return Collection<int, EmailMessage> */
    public function searchMessages(string $query, int $maxResults = 10): Collection;
}
