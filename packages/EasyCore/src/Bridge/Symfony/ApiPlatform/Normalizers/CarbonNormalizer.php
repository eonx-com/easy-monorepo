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
    private const FORMAT_KEY = 'datetime_format';

    /**
     * @var string
     */
    private const TIMEZONE_KEY = 'datetime_timezone';

    /**
     * @var mixed[]
     */
    private $defaultContext = [
        self::FORMAT_KEY => CarbonImmutable::RFC3339,
        self::TIMEZONE_KEY => null,
    ];

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

        $dateTimeFormat = $context[self::FORMAT_KEY] ?? $this->defaultContext[self::FORMAT_KEY];
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
        $dateTimeZone = $context[self::TIMEZONE_KEY] ?? $this->defaultContext[self::TIMEZONE_KEY];

        if ($dateTimeZone === null) {
            return null;
        }

        return $dateTimeZone instanceof CarbonTimeZone ? $dateTimeZone : new CarbonTimeZone($dateTimeZone);
    }
}
