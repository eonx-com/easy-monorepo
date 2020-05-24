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
        $exprLangId = 'security.expression_language';

        if (empty($locations) || $container->has($exprLangId) === false) {
            return;
        }

        $providerClass = PermissionExpressionFunctionProvider::class;
        $providerDef = new Definition($providerClass);
        $providerDef->setArgument('$targets', $locations);

        $container->setDefinition($providerClass, $providerDef);

        $exprLangDef = $container->getDefinition($exprLangId);
        $exprLangDef->addMethodCall('registerProvider', [new Reference($providerClass)]);

        if ($container->hasDefinition('logger')) {
            $providerDef->setArgument('$logger', new Reference('logger'));
        }
    }
}
