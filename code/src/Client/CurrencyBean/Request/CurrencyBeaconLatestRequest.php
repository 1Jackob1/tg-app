<?php

declare(strict_types=1);

namespace App\Client\CurrencyBean\Request;

use App\Client\CurrencyBean\CurrencyBeaconConnector;
use App\Client\CurrencyBean\Response\CurrencyBeaconLatestResponse;
use LogicException;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class CurrencyBeaconLatestRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $base,
        /** @var array<string> */
        private readonly array $symbols,
        private readonly CurrencyBeaconConnector $connector,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/latest';
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        return $this
            ->connector
            ->serializer
            ->deserialize(
                data: $response->body(),
                type: CurrencyBeaconLatestResponse::class,
                format: 'json',
            );
    }

    public function execute(): CurrencyBeaconLatestResponse
    {
        $result = $this->connector->send(request: $this)->dtoOrFail();

        if (!$result instanceof CurrencyBeaconLatestResponse) {
            throw new LogicException('Response DTO must be instance of: ' . CurrencyBeaconLatestResponse::class);
        }

        return $result;
    }

    protected function defaultQuery(): array
    {
        return [
            'base'    => $this->base,
            'symbols' => implode(',', $this->symbols),
        ];
    }
}
