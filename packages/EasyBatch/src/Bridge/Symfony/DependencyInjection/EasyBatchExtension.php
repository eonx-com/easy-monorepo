<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony\DependencyInjection;

use EonX\EasyBatch\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyBatchExtension extends Extension
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

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new PhpFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));

        foreach (self::CONFIGS_TO_PARAMS as $name => $param) {
            $container->setParameter($param, $config[$name]);
        }

        $loader->load('services.php');
    }
}
