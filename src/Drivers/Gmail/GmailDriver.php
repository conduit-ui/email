<?php

namespace ConduitUI\Email\Drivers\Gmail;

use ConduitUI\Email\Contracts\EmailMessage;
use ConduitUI\Email\Contracts\EmailProvider;
use ConduitUI\Email\Drivers\Gmail\Requests\GetMessage;
use ConduitUI\Email\Drivers\Gmail\Requests\ListMessages;
use Illuminate\Support\Collection;

class GmailDriver implements EmailProvider
{
    private GmailConnector $connector;

    public function __construct(string $accessToken)
    {
        $this->connector = new GmailConnector($accessToken);
    }

    public function listMessages(string $query = '', int $maxResults = 10): Collection
    {
        return $this->fetchMessages($query, $maxResults);
    }

    public function getMessage(string $messageId): EmailMessage
    {
        $response = $this->connector->send(new GetMessage($messageId));

        return $this->mapMessage($response->json());
    }

    public function searchMessages(string $query, int $maxResults = 10): Collection
    {
        return $this->fetchMessages($query, $maxResults);
    }

    private function fetchMessages(string $query, int $maxResults): Collection
    {
        $listResponse = $this->connector->send(new ListMessages($query, $maxResults));
        $messageIds = collect($listResponse->json('messages', []))->pluck('id');

        if ($messageIds->isEmpty()) {
            return collect();
        }

        return $messageIds->map(function (string $id) {
            $response = $this->connector->send(new GetMessage($id));

            return $response->successful() ? $this->mapMessage($response->json()) : null;
        })->filter()->values();
    }

    private function mapMessage(array $data): EmailMessage
    {
        $headers = collect($data['payload']['headers'] ?? []);
        $from = $headers->firstWhere('name', 'From')['value'] ?? 'Unknown';
        $fromName = trim(preg_replace('/<[^>]+>/', '', $from), ' "');

        return EmailMessage::from([
            'id' => $data['id'] ?? '',
            'threadId' => $data['threadId'] ?? '',
            'subject' => $headers->firstWhere('name', 'Subject')['value'] ?? 'No subject',
            'from' => $from,
            'fromName' => $fromName !== '' ? $fromName : null,
            'date' => $headers->firstWhere('name', 'Date')['value'] ?? null,
            'snippet' => $data['snippet'] ?? '',
            'labelIds' => $data['labelIds'] ?? [],
        ]);
    }
}
