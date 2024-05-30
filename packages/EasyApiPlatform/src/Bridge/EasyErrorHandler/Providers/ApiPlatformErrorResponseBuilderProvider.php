<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Providers;

use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface;

final class ApiPlatformErrorResponseBuilderProvider implements ErrorResponseBuilderProviderInterface
{
    /**
     * @param iterable<\EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Interface\ApiPlatformErrorResponseBuilderInterface> $builders
     */
    public function __construct(
        private readonly iterable $builders,
    ) {
    }

    /**
     * @return iterable<\EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Interface\ApiPlatformErrorResponseBuilderInterface>
     */
    public function getBuilders(): iterable
    {
        foreach ($this->builders as $builder) {
            yield $builder;
        }
    }
}
