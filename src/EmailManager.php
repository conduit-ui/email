<?php

namespace ConduitUI\Email;

use ConduitUI\Email\Contracts\EmailMessage;
use ConduitUI\Email\Contracts\EmailProvider;
use ConduitUI\Email\Drivers\Gmail\GmailDriver;
use Illuminate\Support\Collection;
use Illuminate\Support\Manager;
use InvalidArgumentException;

class EmailManager extends Manager implements EmailProvider
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('email-client.default', 'gmail');
    }

    public function createGmailDriver(): GmailDriver
    {
        $accessToken = $this->config->get('email-client.drivers.gmail.access_token');

        if (empty($accessToken)) {
            throw new InvalidArgumentException('Gmail access token is not configured. Set GMAIL_ACCESS_TOKEN in your environment.');
        }

        return new GmailDriver($accessToken);
    }

    public function listMessages(string $query = '', int $maxResults = 10): Collection
    {
        return $this->driver()->listMessages($query, $maxResults);
    }

    public function getMessage(string $messageId): EmailMessage
    {
        return $this->driver()->getMessage($messageId);
    }

    public function searchMessages(string $query, int $maxResults = 10): Collection
    {
        return $this->driver()->searchMessages($query, $maxResults);
    }
}
