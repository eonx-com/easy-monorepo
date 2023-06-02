<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Routing;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Api\UrlGeneratorInterface;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\NoIriItemInterface;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\SelfProvidedIriItemInterface;

/**
 * @deprecated since 4.2.8, will be removed in 5.0. Use EonX\EasyApiPlatform\Routing\IriConverter instead.
 */
final class IriConverter implements IriConverterInterface
{
    /**
     * @var \ApiPlatform\Core\Api\IriConverterInterface
     */
    private $decorated;

    public function __construct(IriConverterInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @param mixed $item
     */
    public function getIriFromItem($item, ?int $referenceType = null): string
    {
        if ($item instanceof SelfProvidedIriItemInterface) {
            return $item->getIri();
        }

        if ($item instanceof NoIriItemInterface) {
            return '__iri_not_supported';
        }

        return $this->decorated->getIriFromItem($item, $referenceType ?? UrlGeneratorInterface::ABS_PATH);
    }

    public function getIriFromResourceClass(string $resourceClass, ?int $referenceType = null): string
    {
        return $this->decorated->getIriFromResourceClass(
            $resourceClass,
            $referenceType ?? UrlGeneratorInterface::ABS_PATH
        );
    }

    /**
     * @param null|mixed[] $context
     *
     * @return object
     */
    public function getItemFromIri(string $iri, ?array $context = null)
    {
        return $this->decorated->getItemFromIri($iri, $context ?? []);
    }

    /**
     * @param mixed[] $identifiers
     */
    public function getItemIriFromResourceClass(
        string $resourceClass,
        array $identifiers,
        ?int $referenceType = null
    ): string {
        return $this->decorated->getItemIriFromResourceClass(
            $resourceClass,
            $identifiers,
            $referenceType ?? UrlGeneratorInterface::ABS_PATH
        );
    }

    /**
     * @param mixed[] $identifiers
     */
    public function getSubresourceIriFromResourceClass(
        string $resourceClass,
        array $identifiers,
        ?int $referenceType = null
    ): string {
        return $this->decorated->getSubresourceIriFromResourceClass(
            $resourceClass,
            $identifiers,
            $referenceType ?? UrlGeneratorInterface::ABS_PATH
        );
    }
}
