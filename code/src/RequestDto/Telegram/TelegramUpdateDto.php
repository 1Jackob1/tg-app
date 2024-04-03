<?php

declare(strict_types=1);

namespace App\RequestDto\Telegram;

class TelegramUpdateDto
{
    public int $updateId;
    public TelegramMessageDto $message;
}
