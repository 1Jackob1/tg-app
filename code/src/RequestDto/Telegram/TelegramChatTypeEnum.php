<?php

declare(strict_types=1);

namespace App\RequestDto\Telegram;

enum TelegramChatTypeEnum: string
{
    case PRIVATE    = 'private';
    case GROUP      = 'group';
    case SUPERGROUP = 'supergroup';
    case CHANNEL    = 'channel';
}
