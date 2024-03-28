<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Bridge\Symfony;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyRandomSymfonyBundle extends AbstractBundle
{
    protected string $extensionAlias = 'easy_random';

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__ . '/Resources/config/services.php');
    }
}
