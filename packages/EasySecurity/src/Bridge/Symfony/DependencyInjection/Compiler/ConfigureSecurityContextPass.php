<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use EonX\EasySecurity\MainSecurityContextConfigurator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ConfigureSecurityContextPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $contextServiceId = $this->getParameter($container, BridgeConstantsInterface::PARAM_CONTEXT_SERVICE_ID);

        if ($contextServiceId === null || $container->hasDefinition($contextServiceId) === false) {
            return;
        }

        $container
            ->getDefinition($contextServiceId)
            ->setConfigurator([new Reference(MainSecurityContextConfigurator::class), 'configure']);
    }

    private function getParameter(ContainerBuilder $container, string $param): ?string
    {
        return $container->hasParameter($param) ? $container->getParameter($param) : null;
    }
}
