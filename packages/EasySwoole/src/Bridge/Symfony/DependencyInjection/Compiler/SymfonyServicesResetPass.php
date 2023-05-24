<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasySwoole\Bridge\Symfony\AppStateResetters\SymfonyServicesAppStateResetter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SymfonyServicesResetPass implements CompilerPassInterface
{
    private const SERVICES_RESETTER = 'services_resetter';

    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(self::SERVICES_RESETTER)) {
            $originDef = $container->getDefinition(self::SERVICES_RESETTER);

            $def = $container->getDefinition(SymfonyServicesAppStateResetter::class);
            $def->setArgument('$resettableServices', $originDef->getArgument(0));
            $def->setArgument('$resetMethods', $originDef->getArgument(1));
        }
    }
}
