<?php

declare(strict_types=1);

namespace App\Client\CurrencyBean;

use App\AppParametersContainer;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;
use Symfony\Component\Serializer\SerializerInterface;

class CurrencyBeaconConnector extends Connector
{
    use AlwaysThrowOnErrors;

    private readonly string $baseUri;
    private readonly string $apiKey;

    public function __construct(
        public readonly SerializerInterface $serializer,
        AppParametersContainer $appParametersContainer,
    ) {
        $this->apiKey  = $appParametersContainer->currencyBeaconApiKey;
        $this->baseUri = $appParametersContainer->currencyBeaconBaseUri;
    }

    public function resolveBaseUrl(): string
    {
        return $this->baseUri;
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        return [
            'api_key' => $this->apiKey,
        ];
    }
}
