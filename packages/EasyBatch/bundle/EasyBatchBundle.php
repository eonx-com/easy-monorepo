<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Bundle;

use EonX\EasyBatch\Bundle\CompilerPass\AddMessengerMiddlewareToBusesCompilerPass;
use EonX\EasyBatch\Bundle\CompilerPass\MakeMessengerReceiversPublicCompilerPass;
use EonX\EasyBatch\Bundle\CompilerPass\SetEncryptorOnBatchItemTransformerCompilerPass;
use EonX\EasyBatch\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyBatchBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new SetEncryptorOnBatchItemTransformerCompilerPass())
            ->addCompilerPass(new AddMessengerMiddlewareToBusesCompilerPass(), priority: -10)
            ->addCompilerPass(new MakeMessengerReceiversPublicCompilerPass(), priority: -10);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container
            ->parameters()
            ->set(ConfigParam::BatchClass->value, $config['batch_class'])
            ->set(ConfigParam::BatchItemClass->value, $config['batch_item_class'])
            ->set(ConfigParam::BatchItemPerPage->value, $config['batch_item_per_page'])
            ->set(ConfigParam::BatchItemTable->value, $config['batch_item_table'])
            ->set(ConfigParam::BatchTable->value, $config['batch_table'])
            ->set(ConfigParam::DateTimeFormat->value, $config['date_time_format'])
            ->set(ConfigParam::LockTtl->value, $config['lock_ttl']);

        $container->import('config/services.php');
    }
}
