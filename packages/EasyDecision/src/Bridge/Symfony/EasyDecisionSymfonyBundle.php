<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Symfony;

use EonX\EasyDecision\Bridge\BridgeConstantsInterface;
use EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface;
use EonX\EasyDecision\Interfaces\MappingProviderInterface;
use EonX\EasyDecision\Interfaces\RuleInterface;
use EonX\EasyDecision\Providers\ConfigMappingProvider;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class EasyDecisionSymfonyBundle extends AbstractBundle
{
    protected string $extensionAlias = 'easy_decision';

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
        $container->import(__DIR__ . '/Resources/config/services.php');

        if ($config['use_expression_language'] ?? false) {
            $container->import(__DIR__ . '/Resources/config/use_expression_language.php');
        }

        $builder
            ->registerForAutoconfiguration(DecisionConfiguratorInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_DECISION_CONFIGURATOR);

        $builder
            ->registerForAutoconfiguration(RuleInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_DECISION_RULE);

        $builder
            ->autowire(MappingProviderInterface::class, ConfigMappingProvider::class)
            ->setArgument('$decisionsConfig', $config['type_mapping'] ?? []);

        // Register middleware if messenger present
        if (\class_exists(MessengerPass::class)) {
            $container->import(__DIR__ . '/Resources/config/messenger_middleware.php');
        }
    }
}
