<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ConfigureSecurityContextPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $contextServiceId = $this->getParameter($container, BridgeConstantsInterface::PARAM_CONTEXT_SERVICE_ID);
        $tokenDecoder = $this->getParameter($container, BridgeConstantsInterface::PARAM_TOKEN_DECODER);

        if ($container->hasDefinition($contextServiceId) === false) {
            return;
        }

        $contextDef = $container->getDefinition($contextServiceId);

        $contextDef->setConfigurator()
    }

    private function getParameter(ContainerBuilder $container, string $param): ?string
    {
        return $container->hasParameter($param) ? $container->getParameter($param) : null;
    }
}
