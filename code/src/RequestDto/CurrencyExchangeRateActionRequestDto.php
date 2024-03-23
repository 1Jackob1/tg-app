<?php

declare(strict_types=1);

namespace App\RequestDto;

use Symfony\Component\Validator\Constraints as Assert;

class CurrencyExchangeRateActionRequestDto
{
    #[Assert\Length(3)]
    public string $base;

    /** @var array<string> */
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Length(3),
    ])]
    public array $to;
}
