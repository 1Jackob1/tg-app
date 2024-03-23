<?php

declare(strict_types=1);

namespace App\SymfonySerializer\Normalizer;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * COPY of vendor/symfony/serializer/Normalizer/DateTimeNormalizer.php.
 *
 * Added Carbon support
 *
 * Normalizes an object implementing the {@see DateTimeInterface} to a date string.
 * Denormalizes a date string to an instance of {@see DateTime} or {@see DateTimeImmutable}.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
#[AutoconfigureTag('serializer.normalizer')]
final class CarbonNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public const FORMAT_KEY   = 'datetime_format';
    public const TIMEZONE_KEY = 'datetime_timezone';

    private const SUPPORTED_TYPES = [
        DateTimeInterface::class => true,
        DateTimeImmutable::class => true,
        DateTime::class          => true,
        Carbon::class            => true,
        CarbonImmutable::class   => true,
        CarbonInterface::class   => true,
    ];

    private array $defaultContext = [
        self::FORMAT_KEY   => DateTimeInterface::RFC3339,
        self::TIMEZONE_KEY => null,
    ];

    public function __construct(array $defaultContext = [])
    {
        $this->setDefaultContext($defaultContext);
    }

    public function setDefaultContext(array $defaultContext): void
    {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    public function getSupportedTypes(?string $format): array
    {
        return self::SUPPORTED_TYPES;
    }

    /**
     * @param mixed   $object
     * @param ?string $format
     * @param array   $context
     *
     * @throws InvalidArgumentException
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        if (!$object instanceof DateTimeInterface) {
            throw new InvalidArgumentException('The object must implement the "\DateTimeInterface".');
        }

        $dateTimeFormat = $context[self::FORMAT_KEY] ?? $this->defaultContext[self::FORMAT_KEY];
        $timezone       = $this->getTimezone($context);

        if ($timezone !== null) {
            $object = clone $object;
            $object = $object->setTimezone($timezone);
        }

        return $object->format($dateTimeFormat);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof DateTimeInterface;
    }

    /**
     * @param mixed   $data
     * @param string  $type
     * @param ?string $format
     * @param array   $context
     *
     * @throws NotNormalizableValueException
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): DateTimeInterface
    {
        if (\is_int($data) || \is_float($data)) {
            switch ($context[self::FORMAT_KEY] ?? $this->defaultContext[self::FORMAT_KEY] ?? null) {
                case 'U': $data = sprintf('%d', $data);

                    break;
                case 'U.u': $data = sprintf('%.6F', $data);

                    break;
            }
        }

        if (!\is_string($data) || trim($data) === '') {
            throw NotNormalizableValueException::createForUnexpectedDataType('The data is either not an string, an empty string, or null; you should pass a string that can be parsed with the passed format or a valid DateTime string.', $data, [Type::BUILTIN_TYPE_STRING], $context['deserialization_path'] ?? null, true);
        }

        try {
            if ($type === DateTimeInterface::class) {
                $type = DateTimeImmutable::class;
            }

            $timezone       = $this->getTimezone($context);
            $dateTimeFormat = $context[self::FORMAT_KEY] ?? null;

            if ($dateTimeFormat !== null) {
                if (false !== $object = $type::createFromFormat($dateTimeFormat, $data, $timezone)) {
                    return $object;
                }

                $dateTimeErrors = $type::getLastErrors();

                throw NotNormalizableValueException::createForUnexpectedDataType(sprintf('Parsing datetime string "%s" using format "%s" resulted in %d errors: ', $data, $dateTimeFormat, $dateTimeErrors['error_count']) . "\n" . implode("\n", $this->formatDateTimeErrors($dateTimeErrors['errors'])), $data, [Type::BUILTIN_TYPE_STRING], $context['deserialization_path'] ?? null, true);
            }

            $defaultDateTimeFormat = $this->defaultContext[self::FORMAT_KEY] ?? null;

            if ($defaultDateTimeFormat !== null) {
                if (false !== $object = $type::createFromFormat($defaultDateTimeFormat, $data, $timezone)) {
                    return $object;
                }
            }

            return new $type($data, $timezone);
        } catch (NotNormalizableValueException $e) {
            throw $e;
        } catch (Exception $e) {
            throw NotNormalizableValueException::createForUnexpectedDataType($e->getMessage(), $data, [Type::BUILTIN_TYPE_STRING], $context['deserialization_path'] ?? null, false, $e->getCode(), $e);
        }
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return isset(self::SUPPORTED_TYPES[$type]);
    }

    /**
     * Formats datetime errors.
     *
     * @param array $errors
     *
     * @return string[]
     */
    private function formatDateTimeErrors(array $errors): array
    {
        $formattedErrors = [];

        foreach ($errors as $pos => $message) {
            $formattedErrors[] = sprintf('at position %d: %s', $pos, $message);
        }

        return $formattedErrors;
    }

    private function getTimezone(array $context): ?DateTimeZone
    {
        $dateTimeZone = $context[self::TIMEZONE_KEY] ?? $this->defaultContext[self::TIMEZONE_KEY];

        if ($dateTimeZone === null) {
            return null;
        }

        return $dateTimeZone instanceof DateTimeZone ? $dateTimeZone : new DateTimeZone($dateTimeZone);
    }
}
