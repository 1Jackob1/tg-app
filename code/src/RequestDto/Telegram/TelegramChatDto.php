<?php

namespace App\RequestDto\Telegram;

class TelegramChatDto
{
    public int $id;
    public ?string $firstName = null;
    public ?string $username = null;
    public TelegramChatTypeEnum $type;
}
