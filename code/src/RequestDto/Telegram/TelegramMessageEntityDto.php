<?php

namespace App\RequestDto\Telegram;

class TelegramMessageEntityDto
{
    public TelegramMessageEntityTypeEnum $type;
    public int $offset;
    public int $length;
    public ?string $url = null;
    public ?TelegramUserDto $user = null;
    public ?string $language = null;
    public ?string $customEmojiId = null;
}
