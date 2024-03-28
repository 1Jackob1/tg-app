<?php

namespace App\Client\Telegram\Request;

use App\Client\Telegram\TelegramConnector;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class TelegramSendMessageRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;
    public function __construct(
        private readonly string $text,
        private readonly int $chatId,
        private readonly TelegramConnector $connector,
    )
    {
    }

    public function resolveEndpoint(): string
    {
        return '/sendMessage';
    }

    protected function defaultBody(): array
    {
        return [
            'chat_id' => $this->chatId,
            'text' => $this->text,
        ];
    }

    public function execute(): void
    {
        $this->connector->send(request: $this)->throw();
    }
}
