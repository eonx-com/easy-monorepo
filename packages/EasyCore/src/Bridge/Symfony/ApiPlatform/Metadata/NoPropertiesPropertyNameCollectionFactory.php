<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Metadata;

use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyNameCollection;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\NoPropertiesApiResourceInterface;

final class NoPropertiesPropertyNameCollectionFactory implements PropertyNameCollectionFactoryInterface
{
    /**
     * @var \ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface
     */
    private $decorated;

    public function __construct(PropertyNameCollectionFactoryInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @param null|mixed[] $options
     *
     * @return \ApiPlatform\Core\Metadata\Property\PropertyNameCollection<string>
     *
     * @throws \ApiPlatform\Core\Exception\ResourceClassNotFoundException
     */
    public function create(string $resourceClass, ?array $options = null): PropertyNameCollection
    {
        if (\is_a($resourceClass, NoPropertiesApiResourceInterface::class, true)) {
            return new PropertyNameCollection([]);
        }

        return $this->decorated->create($resourceClass, $options ?? []);
    }
}
