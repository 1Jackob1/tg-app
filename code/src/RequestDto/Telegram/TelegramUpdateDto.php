<?php

namespace App\RequestDto\Telegram;

class TelegramUpdateDto
{
    public int $updateId;
    public TelegramMessageDto $message;
}
