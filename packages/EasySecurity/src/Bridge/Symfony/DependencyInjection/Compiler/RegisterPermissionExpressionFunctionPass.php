<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasySecurity\Bridge\Symfony\Interfaces\ParametersInterface;
use EonX\EasySecurity\Bridge\Symfony\Security\PermissionExpressionFunctionProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterPermissionExpressionFunctionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $locations = $container->getParameter(ParametersInterface::PERMISSIONS_LOCATIONS);

        if (empty($locations) || $container->has('security.expression_language') === false) {
            return;
        }

        $providerClass = PermissionExpressionFunctionProvider::class;
        $providerDef = new Definition($providerClass);

        $container->setDefinition($providerClass, $providerDef);

        $exprLangDef = $container->getDefinition('security.expression_language');
        $exprLangDef->addMethodCall('registerProvider', [new Reference($providerClass)]);
    }
}
