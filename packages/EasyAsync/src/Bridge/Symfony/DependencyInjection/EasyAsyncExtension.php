<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Symfony\DependencyInjection;

use EonX\EasyAsync\Bridge\Symfony\Messenger\ProcessJobLogMiddleware;
use EonX\EasyAsync\Exceptions\InvalidImplementationException;
use EonX\EasyAsync\Interfaces\ImplementationsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class EasyAsyncExtension extends Extension
{
    /**
     * @param mixed[] $configs
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

        $implementation = $config['implementation'] ?? ImplementationsInterface::IMPLEMENTATION_DOCTRINE;

        if (\in_array($implementation, ImplementationsInterface::IMPLEMENTATIONS, true) === false) {
            throw new InvalidImplementationException(\sprintf('Implementation "%s" invalid', $implementation));
        }

        $loader->load(\sprintf('implementations/%s.yaml', $implementation));

        // Register middleware if messenger present
        if (\class_exists(MessengerPass::class)) {
            $container->setDefinition(ProcessJobLogMiddleware::class, new Definition(ProcessJobLogMiddleware::class));
        }
    }
}
