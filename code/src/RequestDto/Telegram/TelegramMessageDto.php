<?php

namespace App\RequestDto\Telegram;

use Carbon\CarbonImmutable;
use Symfony\Component\Serializer\Attribute\Context;

class TelegramMessageDto
{
    public int $messageId;
    public ?int $messageThreadId = null;
    public ?TelegramUserDto $from = null;

    #[Context([
        'datetime_format' => 'U',
    ])]
    public CarbonImmutable $date;
    public TelegramChatDto $chat;
    public string $text;

    /**
     * @var TelegramMessageEntityDto[]
     */
    public array $entities;
}
