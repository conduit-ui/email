<?php

namespace ConduitUI\Email\Contracts;

use Illuminate\Support\Collection;

interface EmailProvider
{
    /**
     * List messages from the inbox.
     *
     * @return Collection<int, EmailMessage>
     */
    public function listMessages(string $query = '', int $maxResults = 10): Collection;

    /**
     * Get a single message by ID.
     */
    public function getMessage(string $messageId): EmailMessage;

    /**
     * Search messages with a query string.
     *
     * @return Collection<int, EmailMessage>
     */
    public function searchMessages(string $query, int $maxResults = 10): Collection;
}
