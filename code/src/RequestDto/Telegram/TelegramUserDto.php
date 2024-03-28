<?php

namespace App\RequestDto\Telegram;

class TelegramUserDto
{
    public int $id;
    public bool $isBot;
    public string $firstName;
    public ?string $lastName = null;
    public ?string $username = null;
    public ?string $languageCode = null;
    public ?true $isPremium = null;
    public ?true $addedToAttachmentMenu = null;
}
