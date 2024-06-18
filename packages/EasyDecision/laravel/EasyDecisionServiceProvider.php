<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Laravel;

use EonX\EasyDecision\Bundle\Enum\ConfigTag;
use EonX\EasyDecision\Configurator\AddRulesDecisionConfigurator;
use EonX\EasyDecision\Configurator\DecisionConfiguratorInterface;
use EonX\EasyDecision\Configurator\SetExpressionLanguageDecisionConfigurator;
use EonX\EasyDecision\Factory\DecisionFactory as BaseDecisionFactory;
use EonX\EasyDecision\Factory\DecisionFactoryInterface as BaseDecisionFactoryInterface;
use EonX\EasyDecision\Factory\ExpressionLanguageFactory;
use EonX\EasyDecision\Factory\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Factory\ExpressionLanguageRuleFactory;
use EonX\EasyDecision\Factory\ExpressionLanguageRuleFactoryInterface;
use EonX\EasyDecision\Provider\ConfigMappingProvider;
use EonX\EasyDecision\Provider\MappingProviderInterface;
use EonX\EasyDecision\Provider\ValueExpressionFunctionProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

final class EasyDecisionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-decision.php' => \base_path('config/easy-decision.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-decision.php', 'easy-decision');

        $this->app->tag(\config('easy-decision.rules', []), [ConfigTag::DecisionRule->value]);

        $this->app->singleton(
            ExpressionLanguageFactoryInterface::class,
            static fn (): ExpressionLanguageFactoryInterface => new ExpressionLanguageFactory()
        );

        $this->app->singleton(ValueExpressionFunctionProvider::class);

        $this->app->singleton(
            AddRulesDecisionConfigurator::class,
            static fn (Container $app): DecisionConfiguratorInterface => new AddRulesDecisionConfigurator(
                $app->tagged(ConfigTag::DecisionRule->value)
            )
        );

        $defaultConfigurators = [AddRulesDecisionConfigurator::class];

        if (\config('easy-decision.use_expression_language', false)) {
            $this->app->bind(
                SetExpressionLanguageDecisionConfigurator::class,
                static fn (Container $app
                ): SetExpressionLanguageDecisionConfigurator => new SetExpressionLanguageDecisionConfigurator(
                    $app->make(ExpressionLanguageFactoryInterface::class)
                )
            );

            $defaultConfigurators[] = SetExpressionLanguageDecisionConfigurator::class;
        }

        $this->app->tag($defaultConfigurators, [ConfigTag::DecisionConfigurator->value]);

        $this->app->singleton(
            MappingProviderInterface::class,
            static fn (): MappingProviderInterface => new ConfigMappingProvider(
                \config('easy-decision.type_mapping', [])
            )
        );

        $this->app->singleton(
            BaseDecisionFactoryInterface::class,
            static fn (Container $app): BaseDecisionFactoryInterface => new BaseDecisionFactory(
                $app->make(MappingProviderInterface::class),
                $app->tagged(ConfigTag::DecisionConfigurator->value)
            )
        );

        $this->app->singleton(ExpressionLanguageRuleFactoryInterface::class, ExpressionLanguageRuleFactory::class);
    }
}
