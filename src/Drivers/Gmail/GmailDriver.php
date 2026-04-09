<?php

namespace ConduitUI\Email\Drivers\Gmail;

use ConduitUI\Email\Contracts\EmailMessage;
use ConduitUI\Email\Contracts\EmailProvider;
use ConduitUI\Email\Drivers\Gmail\Requests\GetMessage;
use ConduitUI\Email\Drivers\Gmail\Requests\ListMessages;
use ConduitUI\Email\Drivers\Gmail\Requests\SearchMessages;
use Illuminate\Support\Collection;

class GmailDriver implements EmailProvider
{
    protected GmailConnector $connector;

    public function __construct(string $accessToken)
    {
        $this->connector = new GmailConnector($accessToken);
    }

    /**
     * @return Collection<int, EmailMessage>
     */
    public function listMessages(string $query = '', int $maxResults = 10): Collection
    {
        $response = $this->connector->send(new ListMessages($query, $maxResults));

        $messages = $response->json('messages') ?? [];

        return collect($messages)->map(function (array $message) {
            return $this->getMessage($message['id']);
        });
    }

    public function getMessage(string $messageId): EmailMessage
    {
        $response = $this->connector->send(new GetMessage($messageId));

        return $this->mapToEmailMessage($response->json());
    }

    /**
     * @return Collection<int, EmailMessage>
     */
    public function searchMessages(string $query, int $maxResults = 10): Collection
    {
        $response = $this->connector->send(new SearchMessages($query, $maxResults));

        $messages = $response->json('messages') ?? [];

        return collect($messages)->map(function (array $message) {
            return $this->getMessage($message['id']);
        });
    }

    /**
     * Map a Gmail API response to an EmailMessage DTO.
     *
     * @param  array<string, mixed>  $data
     */
    protected function mapToEmailMessage(array $data): EmailMessage
    {
        $headers = collect($data['payload']['headers'] ?? []);

        $subject = $headers->firstWhere('name', 'Subject')['value'] ?? '(no subject)';
        $fromRaw = $headers->firstWhere('name', 'From')['value'] ?? '';
        $date = $headers->firstWhere('name', 'Date')['value'] ?? null;

        // Parse "Name <email>" format
        $fromName = null;
        $from = $fromRaw;

        if (preg_match('/^(.+?)\s*<(.+?)>$/', $fromRaw, $matches)) {
            $fromName = trim($matches[1], '" ');
            $from = $matches[2];
        }

        return new EmailMessage(
            id: $data['id'],
            threadId: $data['threadId'] ?? '',
            subject: $subject,
            from: $from,
            fromName: $fromName,
            date: $date,
            snippet: $data['snippet'] ?? '',
            labelIds: $data['labelIds'] ?? [],
        );
    }

    /**
     * Get the underlying Saloon connector for advanced usage.
     */
    public function connector(): GmailConnector
    {
        return $this->connector;
    }

    /**
     * Set a custom connector (useful for testing with MockClient).
     */
    public function setConnector(GmailConnector $connector): static
    {
        $this->connector = $connector;

        return $this;
    }
}
