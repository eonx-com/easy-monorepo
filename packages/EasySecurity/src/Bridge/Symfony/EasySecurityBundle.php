<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony;

use EonX\EasySecurity\Bridge\Symfony\DependencyInjection\Compiler\RegisterPermissionExpressionFunctionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasySecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterPermissionExpressionFunctionPass());
    }
}
