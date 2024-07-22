<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/AdvancedSearchFilter/Serializer/NameConverter/CustomConverter.php
namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\AdvancedSearchFilter\Serializer\NameConverter;
========
namespace EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\NameConverter;
>>>>>>>> refs/heads/6.x:packages/EasyApiPlatform/tests/Fixture/app/src/AdvancedSearchFilter/NameConverter/CustomNameConverter.php

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * Custom converter that will only convert a property named "nameConverted"
 * with the same logic as Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter.
 */
final class CustomNameConverter extends CamelCaseToSnakeCaseNameConverter
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
