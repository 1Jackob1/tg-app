<?php

namespace App\Client\Telegram;

use App\AppParametersContainer;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class TelegramConnector extends Connector
{
    use AlwaysThrowOnErrors;

    private readonly string $baseUri;
    private readonly string $apikey;

    public function __construct(AppParametersContainer $appParametersContainer)
    {
        $this->baseUri = $appParametersContainer->telegramBaseUri;
        $this->apikey = $appParametersContainer->telegramApiKey;
    }

    public function resolveBaseUrl(): string
    {
        return sprintf('%s/bot%s/', $this->baseUri, $this->apikey);
    }
}
