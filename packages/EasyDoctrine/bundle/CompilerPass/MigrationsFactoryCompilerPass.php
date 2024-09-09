<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bundle\CompilerPass;

use EonX\EasyDoctrine\Migration\Factory\MigrationFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class MigrationsFactoryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('doctrine.migrations.migrations_factory') === false) {
            return;
        }

        $container
            ->register('easy_doctrine.migrations.migrations_factory', MigrationFactory::class)
            ->setDecoratedService('doctrine.migrations.migrations_factory')
            ->setArguments([
                new Reference('easy_doctrine.migrations.migrations_factory.inner'),
                new Reference('doctrine'),
                '%kernel.environment%',
            ]);
    }
}
