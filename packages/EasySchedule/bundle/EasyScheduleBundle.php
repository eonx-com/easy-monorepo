<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Bundle;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasySchedule\Bundle\CompilerPass\ScheduleCompilerPass;
use EonX\EasySchedule\Provider\ScheduleProviderInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyScheduleBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ScheduleCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('config/services.php');

        if ($config['clear_entity_manager_on_command_execution'] &&
            \interface_exists(EntityManagerInterface::class)) {
            $container->import('config/doctrine.php');
        }

        $builder
            ->registerForAutoconfiguration(ScheduleProviderInterface::class)
            ->addTag('easy_schedule.schedule_provider');
    }
}
