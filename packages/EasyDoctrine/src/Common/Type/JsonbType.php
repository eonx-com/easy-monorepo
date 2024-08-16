<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Common\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;
use JsonException;

final class JsonbType extends Type
{
    public const NAME = 'jsonb';

    private const FORMAT_DB_JSONB = 'JSONB';

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return \json_encode($value, \JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw InvalidType::new($value, self::class, ['mixed'], $exception);
        }
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (\is_resource($value)) {
            $value = \stream_get_contents($value);
        }

        try {
            $decodedValue = \json_decode((string)$value, true, 512, \JSON_THROW_ON_ERROR);

            return \is_array($decodedValue) ? $this->sortByKey($decodedValue) : $decodedValue;
        } catch (JsonException $exception) {
            throw InvalidFormat::new($value, self::class, 'json', $exception);
        }
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return self::FORMAT_DB_JSONB;
    }

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
