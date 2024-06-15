<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\Serializer\NameConverter;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * Custom converter that will only convert a property named "nameConverted"
 * with the same logic as Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter.
 */
final class CustomConverter extends CamelCaseToSnakeCaseNameConverter
{
    public function denormalize(string $propertyName): string
    {
        return $propertyName === 'name_converted' ? parent::denormalize($propertyName) : $propertyName;
    }

    public function normalize(string $propertyName): string
    {
        return $propertyName === 'nameConverted' ? parent::normalize($propertyName) : $propertyName;
    }
}
