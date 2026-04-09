<?php

use ConduitUI\Email\Contracts\EmailMessage;
use ConduitUI\Email\Drivers\Gmail\GmailConnector;
use ConduitUI\Email\Drivers\Gmail\GmailDriver;
use ConduitUI\Email\Drivers\Gmail\Requests\GetMessage;
use ConduitUI\Email\Drivers\Gmail\Requests\ListMessages;
use ConduitUI\Email\Drivers\Gmail\Requests\SearchMessages;
use ConduitUI\Email\EmailManager;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    $this->mockClient = new MockClient([
        ListMessages::class => MockResponse::make([
            'messages' => [
                ['id' => 'msg-1', 'threadId' => 'thread-1'],
                ['id' => 'msg-2', 'threadId' => 'thread-2'],
            ],
        ]),
        GetMessage::class => MockResponse::make([
            'id' => 'msg-1',
            'threadId' => 'thread-1',
            'snippet' => 'Hey Jordan, just checking in...',
            'labelIds' => ['INBOX', 'UNREAD'],
            'payload' => [
                'headers' => [
                    ['name' => 'Subject', 'value' => 'Weekly Check-in'],
                    ['name' => 'From', 'value' => 'Bob Partridge <bob@example.com>'],
                    ['name' => 'Date', 'value' => 'Mon, 7 Apr 2026 10:00:00 -0700'],
                ],
            ],
        ]),
        SearchMessages::class => MockResponse::make([
            'messages' => [
                ['id' => 'msg-3', 'threadId' => 'thread-3'],
            ],
        ]),
    ]);

    $this->driver = new GmailDriver('test-token');

    $connector = new GmailConnector('test-token');
    $connector->withMockClient($this->mockClient);
    $this->driver->setConnector($connector);
});

it('lists messages from gmail', function () {
    // GetMessage mock needs to handle both msg-1 and msg-2
    $this->mockClient = new MockClient([
        ListMessages::class => MockResponse::make([
            'messages' => [
                ['id' => 'msg-1', 'threadId' => 'thread-1'],
            ],
        ]),
        GetMessage::class => MockResponse::make([
            'id' => 'msg-1',
            'threadId' => 'thread-1',
            'snippet' => 'Hey Jordan...',
            'labelIds' => ['INBOX'],
            'payload' => [
                'headers' => [
                    ['name' => 'Subject', 'value' => 'Test Email'],
                    ['name' => 'From', 'value' => 'sender@example.com'],
                    ['name' => 'Date', 'value' => 'Mon, 7 Apr 2026 10:00:00 -0700'],
                ],
            ],
        ]),
    ]);

    $connector = new GmailConnector('test-token');
    $connector->withMockClient($this->mockClient);
    $this->driver->setConnector($connector);

    $messages = $this->driver->listMessages();

    expect($messages)->toHaveCount(1)
        ->and($messages->first())->toBeInstanceOf(EmailMessage::class)
        ->and($messages->first()->subject)->toBe('Test Email')
        ->and($messages->first()->from)->toBe('sender@example.com');
});

it('gets a single message by id', function () {
    $message = $this->driver->getMessage('msg-1');

    expect($message)->toBeInstanceOf(EmailMessage::class)
        ->and($message->id)->toBe('msg-1')
        ->and($message->threadId)->toBe('thread-1')
        ->and($message->subject)->toBe('Weekly Check-in')
        ->and($message->from)->toBe('bob@example.com')
        ->and($message->fromName)->toBe('Bob Partridge')
        ->and($message->date)->toBe('Mon, 7 Apr 2026 10:00:00 -0700')
        ->and($message->snippet)->toBe('Hey Jordan, just checking in...')
        ->and($message->labelIds)->toBe(['INBOX', 'UNREAD']);
});

it('searches messages with a query', function () {
    $this->mockClient = new MockClient([
        SearchMessages::class => MockResponse::make([
            'messages' => [
                ['id' => 'msg-3', 'threadId' => 'thread-3'],
            ],
        ]),
        GetMessage::class => MockResponse::make([
            'id' => 'msg-3',
            'threadId' => 'thread-3',
            'snippet' => 'Invoice attached',
            'labelIds' => ['INBOX'],
            'payload' => [
                'headers' => [
                    ['name' => 'Subject', 'value' => 'Invoice #1234'],
                    ['name' => 'From', 'value' => 'billing@company.com'],
                    ['name' => 'Date', 'value' => 'Tue, 8 Apr 2026 09:00:00 -0700'],
                ],
            ],
        ]),
    ]);

    $connector = new GmailConnector('test-token');
    $connector->withMockClient($this->mockClient);
    $this->driver->setConnector($connector);

    $messages = $this->driver->searchMessages('from:billing@company.com');

    expect($messages)->toHaveCount(1)
        ->and($messages->first()->subject)->toBe('Invoice #1234')
        ->and($messages->first()->from)->toBe('billing@company.com');
});

it('parses from name and email correctly', function () {
    $message = $this->driver->getMessage('msg-1');

    expect($message->fromName)->toBe('Bob Partridge')
        ->and($message->from)->toBe('bob@example.com');
});

it('handles empty message list', function () {
    $this->mockClient = new MockClient([
        ListMessages::class => MockResponse::make([
            'resultSizeEstimate' => 0,
        ]),
    ]);

    $connector = new GmailConnector('test-token');
    $connector->withMockClient($this->mockClient);
    $this->driver->setConnector($connector);

    $messages = $this->driver->listMessages();

    expect($messages)->toHaveCount(0);
});

it('resolves gmail driver from the manager', function () {
    $manager = app(EmailManager::class);

    expect($manager->getDefaultDriver())->toBe('gmail');
});

it('handles from without angle brackets', function () {
    $this->mockClient = new MockClient([
        GetMessage::class => MockResponse::make([
            'id' => 'msg-plain',
            'threadId' => 'thread-plain',
            'snippet' => 'Plain sender',
            'labelIds' => [],
            'payload' => [
                'headers' => [
                    ['name' => 'Subject', 'value' => 'Plain From'],
                    ['name' => 'From', 'value' => 'plain@example.com'],
                    ['name' => 'Date', 'value' => 'Wed, 9 Apr 2026 08:00:00 -0700'],
                ],
            ],
        ]),
    ]);

    $connector = new GmailConnector('test-token');
    $connector->withMockClient($this->mockClient);
    $this->driver->setConnector($connector);

    $message = $this->driver->getMessage('msg-plain');

    expect($message->from)->toBe('plain@example.com')
        ->and($message->fromName)->toBeNull();
});
