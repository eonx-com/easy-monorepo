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
    private const CONFIGS_TO_PARAMS = [
        'batch_class' => ConfigParam::BatchClass,
        'batch_item_class' => ConfigParam::BatchItemClass,
        'batch_item_per_page' => ConfigParam::BatchItemPerPage,
        'batch_item_table' => ConfigParam::BatchItemTable,
        'batch_table' => ConfigParam::BatchTable,
        'date_time_format' => ConfigParam::DateTimeFormat,
        'lock_ttl' => ConfigParam::LockTtl,
    ];

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
        $definition->import(__DIR__ . '/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach (self::CONFIGS_TO_PARAMS as $name => $param) {
            $container
                ->parameters()
                ->set($param->value, $config[$name]);
        }

        $container->import(__DIR__ . '/config/services.php');
    }
}
