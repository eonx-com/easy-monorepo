<?php

declare(strict_types=1);

namespace EonX\EasySchedule\Bridge\Symfony\DependencyInjection;

use EonX\EasySchedule\Interfaces\ScheduleProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PHPFileLoader;

final class EasyScheduleExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PHPFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $container
            ->registerForAutoconfiguration(ScheduleProviderInterface::class)
            ->addTag('easy_schedule.schedule_provider');
    }
}
