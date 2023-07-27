<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Bridge\Symfony\DependencyInjection;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasySchedule\Interfaces\ScheduleProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyScheduleExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.php');

        if ($config['clear_entity_manager_on_command_execution'] &&
            \interface_exists(EntityManagerInterface::class)) {
            $loader->load('clear_entity_manager_on_command_execution.php');
        }

        $container
            ->registerForAutoconfiguration(ScheduleProviderInterface::class)
            ->addTag('easy_schedule.schedule_provider');
    }
}
