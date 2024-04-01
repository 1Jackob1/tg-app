<?php

declare(strict_types=1);

namespace App\RequestDto\Telegram;

class TelegramChatDto
{
    public int $id;
    public ?string $firstName = null;
    public ?string $username  = null;
    public TelegramChatTypeEnum $type;
}
