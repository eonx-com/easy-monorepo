<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Bridge\Symfony\Stubs;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use EonX\EasyApiPlatform\Tests\Fixtures\App\ApiResource\Dummy;

/**
 * @deprecated Since 5.7, will be removed in 6.0. Not needed anymore since ApiPlatform 3
 */
final class IriConverterStub implements IriConverterInterface
{
    public function getIriFromResource(
        object|string $resource,
        ?int $referenceType = null,
        ?Operation $operation = null,
        ?array $context = null,
    ): ?string {
        return 'some-iri';
    }

    public function getResourceFromIri(string $iri, ?array $context = null, ?Operation $operation = null): object
    {
        return new Dummy();
    }
}
