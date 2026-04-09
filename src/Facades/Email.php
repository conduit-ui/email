<?php

namespace ConduitUI\Email\Facades;

use ConduitUI\Email\EmailManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection listMessages(string $query = '', int $maxResults = 10)
 * @method static \ConduitUI\Email\Contracts\EmailMessage getMessage(string $messageId)
 * @method static \Illuminate\Support\Collection searchMessages(string $query, int $maxResults = 10)
 * @method static \ConduitUI\Email\Contracts\EmailProvider driver(string|null $driver = null)
 *
 * @see EmailManager
 */
class Email extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EmailManager::class;
    }
}
