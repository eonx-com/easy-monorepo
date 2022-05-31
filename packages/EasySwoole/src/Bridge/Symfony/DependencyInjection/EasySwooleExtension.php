<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\DependencyInjection;

use Doctrine\Persistence\ManagerRegistry;
use EonX\EasySwoole\Bridge\BridgeConstantsInterface;
use EonX\EasySwoole\Interfaces\ApplicationStateCheckerInterface;
use EonX\EasySwoole\Interfaces\ApplicationStateResetterInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasySwooleExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));
        $loader->load('services.php');

        $container
            ->registerForAutoconfiguration(ApplicationStateCheckerInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_APP_STATE_CHECKER);

        $container
            ->registerForAutoconfiguration(ApplicationStateResetterInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_APP_STATE_RESETTER);

        if (($config['doctrine']['enabled'] ?? true) && \interface_exists(ManagerRegistry::class)) {
            $loader->load('doctrine.php');
        }
    }
}
