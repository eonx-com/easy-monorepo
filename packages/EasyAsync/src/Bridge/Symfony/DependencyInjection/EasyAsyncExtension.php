<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class EasyAsyncExtension extends Extension
{
    /**
     * Load configuration.
     *
     * @param mixed[] $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        // Set tables parameters
        foreach (['jobs_table', 'job_logs_table'] as $name) {
            $container->setParameter(\sprintf('easy_async_%s', $name), $config[$name]);
        }

        // TODO - Handle invalid implementation
        $loader->load(\sprintf('/implementations/%s.yaml', $config['implementation']));
    }
}
