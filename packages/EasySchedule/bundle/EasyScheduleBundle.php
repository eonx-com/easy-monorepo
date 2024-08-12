<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Bundle;

use Doctrine\Persistence\ManagerRegistry;
use EonX\EasySchedule\Bundle\CompilerPass\ScheduleCompilerPass;
use EonX\EasySchedule\Provider\ScheduleProviderInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
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
        $container->addCompilerPass(new ScheduleCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder
            ->registerForAutoconfiguration(ScheduleProviderInterface::class)
            ->addTag('easy_schedule.schedule_provider');

        $container->import('config/services.php');

        if ($config['clear_entity_manager_on_command_execution']) {
            if (\interface_exists(ManagerRegistry::class) === false) {
                throw new LogicException(
                    'Doctrine Persistence is required to clear entity manager on command execution.'
                );
            }

            $container->import('config/doctrine.php');
        }
    }
}
