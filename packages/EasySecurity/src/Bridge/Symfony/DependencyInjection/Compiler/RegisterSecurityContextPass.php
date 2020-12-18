<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterSecurityContextPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $contextServiceId = $this->getParameter($container, BridgeConstantsInterface::PARAM_CONTEXT_SERVICE_ID);

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
            ->setFactory([new Reference(SecurityContextFactoryInterface::class), 'create']);
    }

    private function getParameter(ContainerBuilder $container, string $param): ?string
    {
        return $container->hasParameter($param) ? $container->getParameter($param) : null;
    }
}
