<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Laravel;

use EonX\EasyDecision\Bridge\BridgeConstantsInterface;
use EonX\EasyDecision\Configurators\AddRulesDecisionConfigurator;
use EonX\EasyDecision\Configurators\SetExpressionLanguageConfigurator;
use EonX\EasyDecision\Decisions\DecisionFactory as BaseDecisionFactory;
use EonX\EasyDecision\Expressions\ExpressionLanguageFactory;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Helpers\ValueExpressionFunctionProvider;
use EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface;
use EonX\EasyDecision\Interfaces\DecisionFactoryInterface as BaseDecisionFactoryInterface;
use EonX\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface;
use EonX\EasyDecision\Interfaces\MappingProviderInterface;
use EonX\EasyDecision\Providers\ConfigMappingProvider;
use EonX\EasyDecision\Rules\ExpressionLanguageRuleFactory;
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

        $this->app->tag(\config('easy-decision.rules', []), [BridgeConstantsInterface::TAG_DECISION_RULE]);

        $this->app->singleton(
            ExpressionLanguageFactoryInterface::class,
            static function (): ExpressionLanguageFactoryInterface {
                return new ExpressionLanguageFactory();
            },
        );

        $this->app->singleton(ValueExpressionFunctionProvider::class);

        $this->app->singleton(
            AddRulesDecisionConfigurator::class,
            static function (Container $app): DecisionConfiguratorInterface {
                return new AddRulesDecisionConfigurator($app->tagged(BridgeConstantsInterface::TAG_DECISION_RULE));
            },
        );

        $defaultConfigurators = [AddRulesDecisionConfigurator::class];

        if (\config('easy-decision.use_expression_language', false)) {
            $this->app->bind(
                SetExpressionLanguageConfigurator::class,
                static function (Container $app): SetExpressionLanguageConfigurator {
                    return new SetExpressionLanguageConfigurator(
                        $app->make(ExpressionLanguageFactoryInterface::class),
                    );
                },
            );

            $defaultConfigurators[] = SetExpressionLanguageConfigurator::class;
        }

        $this->app->tag($defaultConfigurators, [BridgeConstantsInterface::TAG_DECISION_CONFIGURATOR]);

        $this->app->singleton(MappingProviderInterface::class, static function (): MappingProviderInterface {
            return new ConfigMappingProvider(\config('easy-decision.type_mapping', []));
        });

        $this->app->singleton(
            BaseDecisionFactoryInterface::class,
            static function (Container $app): BaseDecisionFactoryInterface {
                return new BaseDecisionFactory(
                    $app->make(MappingProviderInterface::class),
                    $app->tagged(BridgeConstantsInterface::TAG_DECISION_CONFIGURATOR),
                );
            },
        );

        $this->app->singleton(ExpressionLanguageRuleFactoryInterface::class, ExpressionLanguageRuleFactory::class);
    }
}
