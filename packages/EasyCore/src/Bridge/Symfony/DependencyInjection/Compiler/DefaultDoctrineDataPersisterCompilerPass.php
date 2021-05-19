<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection\Compiler;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\DoctrineOrmDataPersister;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DefaultDoctrineDataPersisterCompilerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private const ORIGINAL_ID = 'api_platform.doctrine.orm.data_persister';

    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(self::ORIGINAL_ID) === false) {
            return;
        }

        $container->setDefinition(self::ORIGINAL_ID, $container->getDefinition(DoctrineOrmDataPersister::class));
    }
}
