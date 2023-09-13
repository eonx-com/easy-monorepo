<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony;

use EonX\EasyBatch\Bridge\BridgeConstantsInterface;
use EonX\EasyBatch\Bridge\Symfony\DependencyInjection\Compiler\AddMessengerMiddlewareToBusesCompilerPass;
use EonX\EasyBatch\Bridge\Symfony\DependencyInjection\Compiler\MakeMessengerReceiversPublicCompilerPass;
use EonX\EasyBatch\Bridge\Symfony\DependencyInjection\Compiler\SetEncryptorOnBatchItemTransformerCompilerPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyBatchSymfonyBundle extends AbstractBundle
{
    private const CONFIGS_TO_PARAMS = [
        'batch_class' => BridgeConstantsInterface::PARAM_BATCH_CLASS,
        'batch_item_class' => BridgeConstantsInterface::PARAM_BATCH_ITEM_CLASS,
        'batch_item_per_page' => BridgeConstantsInterface::PARAM_BATCH_ITEM_PER_PAGE,
        'batch_item_table' => BridgeConstantsInterface::PARAM_BATCH_ITEM_TABLE,
        'batch_table' => BridgeConstantsInterface::PARAM_BATCH_TABLE,
        'date_time_format' => BridgeConstantsInterface::PARAM_DATE_TIME_FORMAT,
        'lock_ttl' => BridgeConstantsInterface::PARAM_LOCK_TTL,
    ];

    protected string $extensionAlias = 'easy_batch';

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
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach (self::CONFIGS_TO_PARAMS as $name => $param) {
            $container
                ->parameters()
                ->set($param, $config[$name]);
        }

        $container->import(__DIR__ . '/Resources/config/services.php');
    }
}
