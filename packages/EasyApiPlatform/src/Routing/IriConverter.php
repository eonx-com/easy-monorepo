<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Routing;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Metadata\Operation;

/**
 * @deprecated Since 5.7, will be removed in 6.0. Not needed anymore since ApiPlatform 3
 */
final class IriConverter implements IriConverterInterface
{
    public function __construct(
        private IriConverterInterface $decorated,
    ) {
    }

    public function getIriFromResource(
        mixed $resource,
        ?int $referenceType = null,
        ?Operation $operation = null,
        ?array $context = null,
    ): ?string {
        if ($resource instanceof SelfProvidedIriItemInterface) {
            return $resource->getIri();
        }

        if ($resource instanceof NoIriItemInterface) {
            return '__iri_not_supported';
        }

        return $this->decorated->getIriFromResource(
            $resource,
            $referenceType ?? UrlGeneratorInterface::ABS_PATH,
            $operation,
            $context ?? []
        );
    }

    public function getResourceFromIri(string $iri, ?array $context = null, ?Operation $operation = null): object
    {
        return $this->decorated->getResourceFromIri($iri, $context ?? [], $operation);
    }
}
