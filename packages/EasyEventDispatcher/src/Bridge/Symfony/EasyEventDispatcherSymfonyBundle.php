<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Bridge\Symfony;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyEventDispatcherSymfonyBundle extends AbstractBundle
{
    protected string $extensionAlias = 'easy_event_dispatcher';

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__ . '/Resources/config/services.php');
    }
}
