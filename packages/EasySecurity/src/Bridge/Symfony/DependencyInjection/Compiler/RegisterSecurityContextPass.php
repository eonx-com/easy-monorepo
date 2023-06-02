<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterSecurityContextPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $contextServiceId = $this->getParameter($container);

        if ($contextServiceId === null) {
            return;
        }

        // Clean up existing alias/definition for service context service
        if ($container->hasAlias($contextServiceId)) {
            $container->removeAlias($contextServiceId);
        }

        if ($container->hasDefinition($contextServiceId)) {
            $container->removeDefinition($contextServiceId);
        }

        // Set definition using security context factory
        $container
            ->setDefinition($contextServiceId, new Definition(SecurityContextInterface::class))
            ->setFactory([new Reference(SecurityContextResolverInterface::class), 'resolveContext'])
            ->setPublic(true)
            ->setDeprecated(
                'eonx-com/easy-security',
                '4.1.37',
                'The "%service_id%" service autowiring is deprecated and will be removed in 5.0.' .
                ' Use SecurityContextResolverInterface::resolveContext instead.',
            );

        if ($contextServiceId !== SecurityContextInterface::class) {
            $container->setAlias(SecurityContextInterface::class, $contextServiceId);
        }
    }

    private function getParameter(ContainerBuilder $container): ?string
    {
        if ($container->hasParameter(BridgeConstantsInterface::PARAM_CONTEXT_SERVICE_ID) === false) {
            return null;
        }

        $value = $container->getParameter(BridgeConstantsInterface::PARAM_CONTEXT_SERVICE_ID);

        return \is_string($value) ? $value : null;
    }
}
