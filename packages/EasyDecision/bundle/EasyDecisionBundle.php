<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Bundle;

use EonX\EasyDecision\Bundle\Enum\ConfigTag;
use EonX\EasyDecision\Configurator\DecisionConfiguratorInterface;
use EonX\EasyDecision\Provider\ConfigMappingProvider;
use EonX\EasyDecision\Provider\MappingProviderInterface;
use EonX\EasyDecision\Rule\RuleInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class EasyDecisionBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('config/services.php');

        if ($config['use_expression_language'] ?? false) {
            $container->import('config/use_expression_language.php');
        }

        $builder
            ->registerForAutoconfiguration(DecisionConfiguratorInterface::class)
            ->addTag(ConfigTag::DecisionConfigurator->value);

        $builder
            ->registerForAutoconfiguration(RuleInterface::class)
            ->addTag(ConfigTag::DecisionRule->value);

        $builder
            ->autowire(MappingProviderInterface::class, ConfigMappingProvider::class)
            ->setArgument('$decisionsConfig', $config['type_mapping'] ?? []);

        // Register middleware if messenger present
        if (\class_exists(MessengerPass::class)) {
            $container->import('config/messenger_middleware.php');
        }
    }
}
