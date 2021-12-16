<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use JsonException;

final class JsonbType extends Type
{
    /**
     * @var string
     */
    public const JSONB = 'JSONB';

    /**
     * @param mixed $value
     *
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return \json_encode($value, \JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw ConversionException::conversionFailedSerialization(
                $value,
                $this->getName(),
                $exception->getMessage()
            );
        }
    }

    /**
     * @param mixed $value
     *
     * @return mixed[]|null
     *
     * @throws \Doctrine\DBAL\Types\ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (\is_resource($value)) {
            $value = \stream_get_contents($value);
        }

        try {
            $decodedValue = \json_decode($value, true, 512, \JSON_THROW_ON_ERROR);

            return \is_array($decodedValue) ? $this->sortByKey($decodedValue) : $decodedValue;
        } catch (JsonException $exception) {
            throw ConversionException::conversionFailed($value, $this->getName(), $exception);
        }
    }

    public function getName(): string
    {
        return self::JSONB;
    }

    /**
     * @param mixed[] $column
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDoctrineTypeMapping(self::JSONB);
    }

    /**
     * @param mixed[] $array
     *
     * @return mixed[]
     */
    private function sortByKey(array $array): array
    {
        \ksort($array);

        foreach ($array as $key => $value) {
            if (\is_array($value)) {
                $array[$key] = $this->sortByKey($value);
            }
        }

        return $array;
    }
}
