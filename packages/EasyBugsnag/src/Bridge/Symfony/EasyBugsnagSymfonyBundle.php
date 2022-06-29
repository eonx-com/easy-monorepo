<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony;

use EonX\EasyBugsnag\Bridge\Symfony\DependencyInjection\Compiler\DoctrineOrmSqlLoggerConfiguratorPass;
use EonX\EasyBugsnag\Bridge\Symfony\DependencyInjection\Compiler\SensitiveDataSanitizerCompilerPass;
use EonX\EasyBugsnag\Bridge\Symfony\DependencyInjection\EasyBugsnagExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyBugsnagSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new DoctrineOrmSqlLoggerConfiguratorPass())
            ->addCompilerPass(new SensitiveDataSanitizerCompilerPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyBugsnagExtension();
    }
}
