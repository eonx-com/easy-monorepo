<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\ApiPlatform\Metadata;

use ApiPlatform\Core\Bridge\Symfony\PropertyInfo\Metadata\Property\PropertyInfoPropertyNameCollectionFactory;
use ApiPlatform\Core\Exception\RuntimeException;
use ApiPlatform\Core\Metadata\Property\PropertyNameCollection;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Metadata\NoPropertiesPropertyNameCollectionFactory;
use EonX\EasyCore\Tests\AbstractTestCase;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\NoPropertiesNoInterfaceStub;
use EonX\EasyCore\Tests\Bridge\Symfony\Stubs\NoPropertiesWithInterfaceStub;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

final class NoPropertiesPropertyNameCollectionFactoryTest extends AbstractTestCase
{
    public function testCreateWithNoPropertiesException(): void
    {
        $this->expectException(RuntimeException::class);

        $factory = new NoPropertiesPropertyNameCollectionFactory(
            new PropertyInfoPropertyNameCollectionFactory(
                new PropertyInfoExtractor()
            )
        );

        $factory->create(NoPropertiesNoInterfaceStub::class);
    }

    public function testCreateWithNoPropertiesWithInterface(): void
    {
        $factory = new NoPropertiesPropertyNameCollectionFactory(
            new PropertyInfoPropertyNameCollectionFactory(
                new PropertyInfoExtractor()
            )
        );

        self::assertInstanceOf(PropertyNameCollection::class, $factory->create(NoPropertiesWithInterfaceStub::class));
    }
}
