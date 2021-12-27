<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Normalizers;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonTimeZone;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class CarbonNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @var string
     */
    private const DEFAULT_FORMAT = CarbonImmutable::RFC3339;

    /**
     * @var string
     */
    private const DEFAULT_TIMEZONE = 'UTC';

    /**
     * @var string
     */
    private const FORMAT_KEY = 'datetime_format';

    /**
     * @var string
     */
    private const TIMEZONE_KEY = 'datetime_timezone';

    /**
     * @var string
     */
    private $defaultFormat;

    /**
     * @var string
     */
    private $defaultTimezone;

    public function __construct(?string $format = null, ?string $timezone = null)
    {
        $this->defaultFormat = $format ?? self::DEFAULT_FORMAT;
        $this->defaultTimezone = $timezone ?? self::DEFAULT_TIMEZONE;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @param mixed $object
     * @param mixed[]|null $context
     */
    public function normalize($object, ?string $format = null, ?array $context = null): string
    {
        if ($object instanceof CarbonInterface === false) {
            throw new InvalidArgumentException('The object must implement the "\Carbon\CarbonInterface".');
        }

        $dateTimeFormat = $context[self::FORMAT_KEY] ?? $this->defaultFormat;
        $timezone = $this->getTimezone($context);

        if ($timezone !== null) {
            $object = clone $object;
            $object = $object->setTimezone($timezone);
        }

        return $object->format($dateTimeFormat);
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof CarbonInterface;
    }

    /**
     * @param mixed[]|null $context
     */
    private function getTimezone(?array $context = null): ?CarbonTimeZone
    {
        $dateTimeZone = $context[self::TIMEZONE_KEY] ?? $this->defaultTimezone;

        if ($dateTimeZone === null) {
            return null;
        }

        return $dateTimeZone instanceof CarbonTimeZone ? $dateTimeZone : new CarbonTimeZone($dateTimeZone);
    }
}
