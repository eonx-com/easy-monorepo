<?php
declare(strict_types=1);

namespace EonX\EasySchedule\Bridge\Symfony;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasySchedule\Bridge\Symfony\DependencyInjection\Compiler\SchedulePass;
use EonX\EasySchedule\Interfaces\ScheduleProviderInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyScheduleSymfonyBundle extends AbstractBundle
{
    protected string $extensionAlias = 'easy_schedule';

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new SchedulePass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__ . '/Resources/config/services.php');

        if ($config['clear_entity_manager_on_command_execution'] &&
            \interface_exists(EntityManagerInterface::class)) {
            $container->import(__DIR__ . '/Resources/config/clear_entity_manager_on_command_execution.php');
        }

        $builder
            ->registerForAutoconfiguration(ScheduleProviderInterface::class)
            ->addTag('easy_schedule.schedule_provider');
    }
}
