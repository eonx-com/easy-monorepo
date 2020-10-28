<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Metadata;

use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyNameCollection;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\NoPropertiesApiResourceInterface;

/**
 * @deprecated Since 2.4, will be removed in 3.0. This is now supported by api-platform itself.
 */
final class NoPropertiesPropertyNameCollectionFactory implements PropertyNameCollectionFactoryInterface
{
    /**
     * @var \ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface
     */
    private $decorated;

    public function __construct(PropertyNameCollectionFactoryInterface $decorated)
    {
        $this->decorated = $decorated;

        @\trigger_error(\sprintf(
            '%s is deprecated since 2.4.26 and will be removed in 3.0, This is now supported by api-platform itself.',
            static::class,
        ), \E_USER_DEPRECATED);
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
