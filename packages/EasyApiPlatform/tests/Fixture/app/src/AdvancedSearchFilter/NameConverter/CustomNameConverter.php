<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\NameConverter;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * Custom converter that will only convert a property named "nameConverted"
 * with the same logic as Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter.
 */
final class CustomNameConverter extends CamelCaseToSnakeCaseNameConverter
{
    public function denormalize(
        string $propertyName,
        ?string $class = null,
        ?string $format = null,
        ?array $context = null
    ): string {
        return $propertyName === 'name_converted'
            ? parent::denormalize($propertyName, $class, $format, $context ?? [])
            : $propertyName;
    }

    public function normalize(
        string $propertyName,
        ?string $class = null,
        ?string $format = null,
        ?array $context = null
    ): string {
        return $propertyName === 'nameConverted'
            ? parent::normalize($propertyName, $class, $format, $context ?? [])
            : $propertyName;
    }
}
