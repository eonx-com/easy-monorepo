<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Symfony\DependencyInjection;

use EonX\EasyDecision\Bridge\BridgeConstantsInterface;
use EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface;
use EonX\EasyDecision\Interfaces\MappingProviderInterface;
use EonX\EasyDecision\Interfaces\RuleInterface;
use EonX\EasyDecision\Providers\ConfigMappingProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class EasyDecisionExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        if ($config['use_expression_language'] ?? false) {
            $loader->load('use_expression_language.php');
        }

        $container
            ->registerForAutoconfiguration(DecisionConfiguratorInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_DECISION_CONFIGURATOR);

        $container
            ->registerForAutoconfiguration(RuleInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_DECISION_RULE);

        $container
            ->autowire(MappingProviderInterface::class, ConfigMappingProvider::class)
            ->setArgument('$decisionsConfig', $config['type_mapping'] ?? []);

        // Register middleware if messenger present
        if (\class_exists(MessengerPass::class)) {
            $loader->load('messenger_middleware.php');
        }
    }
}
