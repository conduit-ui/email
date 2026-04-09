# Email

Provider-agnostic email client for Laravel. Gmail first, extensible to Outlook, IMAP, and more.

[![CI](https://github.com/conduit-ui/email/actions/workflows/ci.yml/badge.svg)](https://github.com/conduit-ui/email/actions/workflows/ci.yml)

## Installation

```bash
composer require conduit-ui/email
```

## Configuration

Publish the config:

```bash
php artisan vendor:publish --tag=email-config
```

Set your Gmail access token:

```env
EMAIL_DRIVER=gmail
GMAIL_ACCESS_TOKEN=your-token
```

## Usage

```php
use ConduitUI\Email\EmailManager;

$email = app(EmailManager::class);

// List recent unread messages
$messages = $email->driver()->listMessages('is:unread', maxResults: 10);

// Each message is an EmailMessage DTO
foreach ($messages as $message) {
    echo "{$message->fromName}: {$message->subject}";
    echo PHP_EOL;
}

// Get a specific message
$message = $email->driver()->getMessage('message-id');

// Search with Gmail syntax
$results = $email->driver()->searchMessages('from:boss@company.com has:attachment');
```

## EmailMessage DTO

```php
ConduitUI\Email\Contracts\EmailMessage {
    string $id
    string $threadId
    string $subject
    string $from
    ?string $fromName
    ?string $date
    string $snippet
    array $labelIds
}
```

## Adding Drivers

Implement `EmailProvider`:

```php
use ConduitUI\Email\Contracts\EmailProvider;

class OutlookDriver implements EmailProvider
{
    public function listMessages(string $query = '', int $maxResults = 10): Collection { ... }
    public function getMessage(string $messageId): EmailMessage { ... }
    public function searchMessages(string $query, int $maxResults = 10): Collection { ... }
}
```

Register in the `EmailManager` or extend via config.

## Requirements

- PHP 8.2+
- Laravel 12 or 13
- Saloon 4.x
- Spatie Laravel Data 4.x

## License

MIT
