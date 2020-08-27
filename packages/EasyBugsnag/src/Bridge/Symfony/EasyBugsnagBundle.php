<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony;

use EonX\EasyBugsnag\Bridge\Symfony\DependencyInjection\Compiler\DoctrineOrmSqlLoggerConfiguratorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyBugsnagBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new DoctrineOrmSqlLoggerConfiguratorPass());
    }
}
