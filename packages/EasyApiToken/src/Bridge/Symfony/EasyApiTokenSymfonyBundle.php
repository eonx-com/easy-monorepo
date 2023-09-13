<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Bridge\Symfony;

use EonX\EasyApiToken\Bridge\BridgeConstantsInterface;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyApiTokenSymfonyBundle extends AbstractBundle
{
    protected string $extensionAlias = 'easy_api_token';

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__ . '/Resources/config/services.php');

        $builder
            ->registerForAutoconfiguration(ApiTokenDecoderProviderInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_DECODER_PROVIDER);
    }
}
