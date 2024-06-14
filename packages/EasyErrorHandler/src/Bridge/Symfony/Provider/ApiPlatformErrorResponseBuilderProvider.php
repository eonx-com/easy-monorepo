<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Provider;

use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderProviderInterface;

final class ApiPlatformErrorResponseBuilderProvider implements ErrorResponseBuilderProviderInterface
{
    /**
     * @param iterable<\EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\ApiPlatformErrorResponseBuilderInterface> $builders
     */
    public function __construct(
        private readonly iterable $builders,
    ) {
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\ApiPlatformErrorResponseBuilderInterface>
     */
    public function getBuilders(): iterable
    {
        foreach ($this->builders as $builder) {
            yield $builder;
        }
    }
}
