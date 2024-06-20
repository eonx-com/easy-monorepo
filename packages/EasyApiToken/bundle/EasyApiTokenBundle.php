<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Bundle;

use EonX\EasyApiToken\Bundle\Enum\ConfigTag;
use EonX\EasyApiToken\Common\Provider\DecoderProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyApiTokenBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__ . '/config/services.php');

        $builder
            ->registerForAutoconfiguration(DecoderProviderInterface::class)
            ->addTag(ConfigTag::DecoderProvider->value);
    }
}
