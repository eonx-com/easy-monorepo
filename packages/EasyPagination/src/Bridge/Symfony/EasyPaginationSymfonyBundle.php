<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Symfony;

use EonX\EasyPagination\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyPaginationSymfonyBundle extends AbstractBundle
{
    private const PAGINATION_PARAMS = [
        'page_attribute' => BridgeConstantsInterface::PARAM_PAGE_ATTRIBUTE,
        'page_default' => BridgeConstantsInterface::PARAM_PAGE_DEFAULT,
        'per_page_attribute' => BridgeConstantsInterface::PARAM_PER_PAGE_ATTRIBUTE,
        'per_page_default' => BridgeConstantsInterface::PARAM_PER_PAGE_DEFAULT,
    ];

    protected string $extensionAlias = 'easy_pagination';

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach (self::PAGINATION_PARAMS as $name => $param) {
            $container
                ->parameters()
                ->set($param, $config['pagination'][$name]);
        }

        $container->import(__DIR__ . '/Resources/config/services.php');

        if ($config['use_default_resolver'] ?? true) {
            $container->import(__DIR__ . '/Resources/config/default_resolver.php');
        }
    }
}
