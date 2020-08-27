<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\DependencyInjection\Compiler;

use Bugsnag\Client;
use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Bridge\Doctrine\DBAL\SqlLoggerConfigurator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class DoctrineOrmSqlLoggerConfiguratorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $enabled = $container->hasParameter(BridgeConstantsInterface::PARAM_DOCTRINE_ORM)
            ? $container->getParameter(BridgeConstantsInterface::PARAM_DOCTRINE_ORM)
            : false;

        if ($enabled === false
            || \class_exists(EntityManagerInterface::class) === false
            || $container->hasDefinition(EntityManagerInterface::class) === false) {
            return;
        }

        $container->setDefinition(
            SqlLoggerConfigurator::class,
            new Definition(SqlLoggerConfigurator::class, [new Reference(Client::class)])
        );

        $container
            ->getDefinition(EntityManagerInterface::class)
            ->setConfigurator([new Reference(SqlLoggerConfigurator::class), 'configure']);
    }
}
