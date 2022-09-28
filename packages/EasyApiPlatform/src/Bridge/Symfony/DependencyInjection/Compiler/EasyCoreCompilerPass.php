<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @deprecated since 4.2.8, will be removed in 5.0.
 */
final class EasyCoreCompilerPass implements CompilerPassInterface
{
    private const SERVICE_ID_IRI_CONVERTER = 'EonX\EasyCore\Bridge\Symfony\ApiPlatform\Routing\IriConverter';

    private const SERVICE_ID_REQUEST_DATA_COLLECTOR = 'EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataCollector\RequestDataCollector';

    public function process(ContainerBuilder $container): void
    {
        if ($container->has(self::SERVICE_ID_IRI_CONVERTER)) {
            $container->removeDefinition(self::SERVICE_ID_IRI_CONVERTER);
        }

        if ($container->has(self::SERVICE_ID_REQUEST_DATA_COLLECTOR)) {
            $container->removeDefinition(self::SERVICE_ID_REQUEST_DATA_COLLECTOR);
        }
    }
}
