<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\DependencyInjection;

use EonX\EasyAsync\Bridge\BridgeConstantsInterface;
use EonX\EasyAsync\Bridge\Symfony\Messenger\ProcessJobLogMiddleware;
use EonX\EasyAsync\Exceptions\InvalidImplementationException;
use EonX\EasyAsync\Interfaces\ImplementationsInterface;
use EonX\EasyAsync\Interfaces\JobLogFactoryInterface;
use EonX\EasyAsync\Interfaces\JobLogPersisterInterface;
use EonX\EasyAsync\Interfaces\JobLogUpdaterInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class EasyAsyncExtension extends Extension
{
    /**
     * @var string[]
     */
    private const BATCH_PARAMS = [
        'batches_table' => BridgeConstantsInterface::PARAM_BATCHES_TABLE,
        'batch_items_table' => BridgeConstantsInterface::PARAM_BATCH_ITEMS_TABLE,
        'messenger_buses' => BridgeConstantsInterface::PARAM_BATCH_MESSENGER_BUSES,
    ];

    /**
     * @var mixed[]
     */
    private $config;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $container;

    /**
     * @var \Symfony\Component\DependencyInjection\Loader\PhpFileLoader
     */
    private $loader;

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->config = $this->processConfiguration(new Configuration(), $configs);
        $this->container = $container;
        $this->loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $this->batch();
        $this->jobLog();
    }

    private function batch(): void
    {
        foreach (self::BATCH_PARAMS as $config => $param) {
            $this->container->setParameter($param, $this->config['batch'][$config] ?? null);
        }

        $this->loader->load('batch.php');
    }

    private function jobLog(): void
    {
        $this->loader->load('services.php');

        // Set tables parameters
        foreach (['jobs_table', 'job_logs_table'] as $name) {
            $this->container->setParameter(\sprintf('easy_async_%s', $name), $this->config[$name]);
        }

        $implementation = $this->config['implementation'] ?? ImplementationsInterface::IMPLEMENTATION_DOCTRINE;

        if (\in_array($implementation, ImplementationsInterface::IMPLEMENTATIONS, true) === false) {
            throw new InvalidImplementationException(\sprintf('Implementation "%s" invalid', $implementation));
        }

        $this->loader->load(\sprintf('implementations/%s.php', $implementation));

        // Register middleware if messenger present
        if (\class_exists(MessengerPass::class)) {
            $jobLogMidDef = new Definition(ProcessJobLogMiddleware::class);
            $jobLogMidDef->addMethodCall('setJogLogFactory', [new Reference(JobLogFactoryInterface::class)]);
            $jobLogMidDef->addMethodCall('setJobLogPersister', [new Reference(JobLogPersisterInterface::class)]);
            $jobLogMidDef->addMethodCall('setJobLogUpdater', [new Reference(JobLogUpdaterInterface::class)]);

            $this->container->setDefinition(ProcessJobLogMiddleware::class, $jobLogMidDef);
        }
    }
}
