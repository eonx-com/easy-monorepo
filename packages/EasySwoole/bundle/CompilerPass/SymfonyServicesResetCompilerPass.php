<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bundle\CompilerPass;

use EonX\EasySwoole\Common\Resetter\SymfonyServicesAppStateResetter;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SymfonyServicesResetCompilerPass implements CompilerPassInterface
{
    private const SERVICES_RESETTER = 'services_resetter';

    public function process(ContainerBuilder $container): void
    {
        $symfonyServicesAppStateResetter = $container->getDefinition(SymfonyServicesAppStateResetter::class);

        if ($container->hasDefinition(self::SERVICES_RESETTER)) {
            $servicesResetter = $container->getDefinition(self::SERVICES_RESETTER);

            // It is important to call `setArguments` instead of `setArgument` here
            $symfonyServicesAppStateResetter->setArguments([
                $servicesResetter->getArgument(0),
                $servicesResetter->getArgument(1),
            ]);
            $symfonyServicesAppStateResetter->setPublic(true);

            $container->setDefinition(self::SERVICES_RESETTER, $symfonyServicesAppStateResetter);
            $container->removeDefinition(SymfonyServicesAppStateResetter::class);
        }

        if ($container->hasDefinition(self::SERVICES_RESETTER) === false) {
            $symfonyServicesAppStateResetter->setArgument('$resettableServices', new IteratorArgument([]));
            $symfonyServicesAppStateResetter->setArgument('$resetMethods', []);
        }
    }
}
