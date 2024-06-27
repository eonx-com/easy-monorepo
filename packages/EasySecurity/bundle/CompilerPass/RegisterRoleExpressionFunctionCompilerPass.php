<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bundle\CompilerPass;

use EonX\EasySecurity\Bundle\Enum\BundleParam;
use EonX\EasySecurity\Bundle\Enum\ConfigParam;
use EonX\EasySecurity\SymfonySecurity\Provider\RoleExpressionFunctionProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterRoleExpressionFunctionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $locations = (array)$container->getParameter(ConfigParam::RolesLocations->value);
        $exprLangId = 'security.expression_language';

        if (\count($locations) === 0 || $container->has($exprLangId) === false) {
            return;
        }

        $providerClass = RoleExpressionFunctionProvider::class;
        $providerDef = new Definition($providerClass);
        $providerDef->setArgument('$locations', $locations);

        $container->setDefinition($providerClass, $providerDef);

        $exprLangDef = $container->getDefinition($exprLangId);
        $exprLangDef->addMethodCall('registerProvider', [new Reference($providerClass)]);

        if ($container->hasDefinition('logger')) {
            $providerDef
                ->setArgument('$logger', new Reference('logger'))
                ->addTag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);
        }
    }
}
