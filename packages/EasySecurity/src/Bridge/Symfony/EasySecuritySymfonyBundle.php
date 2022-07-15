<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony;

use EonX\EasySecurity\Bridge\Symfony\DependencyInjection\Compiler\RegisterPermissionExpressionFunctionPass;
use EonX\EasySecurity\Bridge\Symfony\DependencyInjection\Compiler\RegisterRoleExpressionFunctionPass;
use EonX\EasySecurity\Bridge\Symfony\DependencyInjection\Compiler\RegisterSecurityContextPass;
use EonX\EasySecurity\Bridge\Symfony\DependencyInjection\EasySecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasySecuritySymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterPermissionExpressionFunctionPass());
        $container->addCompilerPass(new RegisterRoleExpressionFunctionPass());
        $container->addCompilerPass(new RegisterSecurityContextPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasySecurityExtension();
    }
}
