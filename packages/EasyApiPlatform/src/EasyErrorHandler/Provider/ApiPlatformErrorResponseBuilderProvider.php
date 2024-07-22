<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Provider;

use EonX\EasyErrorHandler\Common\Provider\ErrorResponseBuilderProviderInterface;

final readonly class ApiPlatformErrorResponseBuilderProvider implements ErrorResponseBuilderProviderInterface
{
    /**
     * @param iterable<\EonX\EasyApiPlatform\EasyErrorHandler\Interface\ApiPlatformErrorResponseBuilderInterface> $builders
     */
    public function __construct(
        private iterable $builders,
    ) {
    }

    /**
     * @return iterable<\EonX\EasyApiPlatform\EasyErrorHandler\Interface\ApiPlatformErrorResponseBuilderInterface>
     */
    public function getBuilders(): iterable
    {
        foreach ($this->builders as $builder) {
            yield $builder;
        }
    }
}
