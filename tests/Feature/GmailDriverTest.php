<?php

use ConduitUI\Email\Contracts\EmailMessage;
use ConduitUI\Email\Drivers\Gmail\GmailConnector;
use ConduitUI\Email\Drivers\Gmail\GmailDriver;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

describe('GmailDriver', function () {
    function makeDriver(MockClient $mockClient): GmailDriver
    {
        $connector = new GmailConnector('fake-token');
        $connector->withMockClient($mockClient);

        $driver = new GmailDriver('fake-token');
        $ref = new ReflectionProperty($driver, 'connector');
        $ref->setValue($driver, $connector);

        return $driver;
    }

    it('lists messages and returns EmailMessage DTOs', function () {
        $driver = makeDriver(new MockClient([
            '*/users/me/messages' => MockResponse::make([
                'messages' => [
                    ['id' => 'msg-1'],
                    ['id' => 'msg-2'],
                ],
            ]),
            '*/users/me/messages/msg-1' => MockResponse::make([
                'id' => 'msg-1',
                'threadId' => 'thread-1',
                'snippet' => 'Hey Jordan, quick question...',
                'labelIds' => ['INBOX', 'UNREAD'],
                'payload' => [
                    'headers' => [
                        ['name' => 'Subject', 'value' => 'Quick question'],
                        ['name' => 'From', 'value' => 'Chris Smith <chris@example.com>'],
                        ['name' => 'Date', 'value' => 'Wed, 9 Apr 2026 10:00:00 -0700'],
                    ],
                ],
            ]),
            '*/users/me/messages/msg-2' => MockResponse::make([
                'id' => 'msg-2',
                'threadId' => 'thread-2',
                'snippet' => 'Invoice attached',
                'labelIds' => ['INBOX'],
                'payload' => [
                    'headers' => [
                        ['name' => 'Subject', 'value' => 'Invoice #4521'],
                        ['name' => 'From', 'value' => 'billing@vendor.com'],
                        ['name' => 'Date', 'value' => 'Wed, 9 Apr 2026 08:00:00 -0700'],
                    ],
                ],
            ]),
        ]));

        $messages = $driver->listMessages('is:unread', 10);

        expect($messages)->toHaveCount(2);
        expect($messages[0])->toBeInstanceOf(EmailMessage::class);
        expect($messages[0]->subject)->toBe('Quick question');
        expect($messages[0]->fromName)->toBe('Chris Smith');
        expect($messages[0]->snippet)->toContain('Hey Jordan');

        expect($messages[1]->subject)->toBe('Invoice #4521');
        expect($messages[1]->fromName)->toBe('billing@vendor.com');
    });

    it('returns empty collection when no messages', function () {
        $driver = makeDriver(new MockClient([
            '*' => MockResponse::make(['messages' => null]),
        ]));

        $messages = $driver->listMessages('is:unread');

        expect($messages)->toBeEmpty();
    });

    it('gets a single message by ID', function () {
        $driver = makeDriver(new MockClient([
            '*' => MockResponse::make([
                'id' => 'msg-abc',
                'threadId' => 'thread-abc',
                'snippet' => 'Meeting tomorrow at 3pm',
                'labelIds' => ['INBOX'],
                'payload' => [
                    'headers' => [
                        ['name' => 'Subject', 'value' => 'Meeting reminder'],
                        ['name' => 'From', 'value' => '"Boss Man" <boss@company.com>'],
                        ['name' => 'Date', 'value' => 'Wed, 9 Apr 2026 15:00:00 -0700'],
                    ],
                ],
            ]),
        ]));

        $message = $driver->getMessage('msg-abc');

        expect($message)->toBeInstanceOf(EmailMessage::class);
        expect($message->id)->toBe('msg-abc');
        expect($message->subject)->toBe('Meeting reminder');
        expect($message->fromName)->toBe('Boss Man');
    });

    it('parses plain email addresses without name', function () {
        $driver = makeDriver(new MockClient([
            '*' => MockResponse::make([
                'id' => 'msg-plain',
                'threadId' => 'thread-plain',
                'snippet' => 'Test',
                'labelIds' => [],
                'payload' => [
                    'headers' => [
                        ['name' => 'Subject', 'value' => 'Test'],
                        ['name' => 'From', 'value' => 'noreply@system.com'],
                    ],
                ],
            ]),
        ]));

        $message = $driver->getMessage('msg-plain');

        expect($message->from)->toBe('noreply@system.com');
        expect($message->fromName)->toBe('noreply@system.com');
    });
});
