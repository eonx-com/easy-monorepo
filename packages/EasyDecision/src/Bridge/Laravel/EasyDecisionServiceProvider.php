<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyDecision\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use LoyaltyCorp\EasyDecision\Bridge\Common\DecisionFactory as BridgeDecisionFactory;
use LoyaltyCorp\EasyDecision\Bridge\Common\ExpressionLanguageConfigFactory;
use LoyaltyCorp\EasyDecision\Bridge\Common\Interfaces\DecisionFactoryInterface;
use LoyaltyCorp\EasyDecision\Bridge\Common\Interfaces\ExpressionLanguageConfigFactoryInterface;
use LoyaltyCorp\EasyDecision\Decisions\DecisionFactory as BaseDecisionFactory;
use LoyaltyCorp\EasyDecision\Expressions\ExpressionFunctionFactory;
use LoyaltyCorp\EasyDecision\Expressions\ExpressionLanguageFactory;
use LoyaltyCorp\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface;
use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface;
use LoyaltyCorp\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface;
use LoyaltyCorp\EasyDecision\Rules\ExpressionLanguageRuleFactory;

final class EasyDecisionServiceProvider extends ServiceProvider
{
    /**
     * Publish configuration file.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-decision.php' => \base_path('config/easy-decision.php')
        ]);
    }

    /**
     * Register EasyDecision services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-decision.php', 'easy-decision');

        $this->app->singleton(ExpressionFunctionFactoryInterface::class, ExpressionFunctionFactory::class);
        $this->app->singleton(ExpressionLanguageConfigFactoryInterface::class, ExpressionLanguageConfigFactory::class);
        $this->app->singleton(ExpressionLanguageFactoryInterface::class, ExpressionLanguageFactory::class);
        $this->app->singleton(ExpressionLanguageRuleFactoryInterface::class, ExpressionLanguageRuleFactory::class);

        $this->app->singleton(
            ExpressionLanguageConfigFactoryInterface::class,
            function (): ExpressionLanguageConfigFactoryInterface {
                return new ExpressionLanguageConfigFactory(\config('easy-decision', []), $this->app);
            }
        );

        $this->app->singleton(DecisionFactoryInterface::class, function (): DecisionFactoryInterface {
            $baseFactory = new BaseDecisionFactory(
                $this->app->make(ExpressionLanguageFactoryInterface::class)
            );

            return new BridgeDecisionFactory(\config('easy-decision', []), $this->app, $baseFactory);
        });
    }
}

\class_alias(
    EasyDecisionServiceProvider::class,
    'StepTheFkUp\EasyDecision\Bridge\Laravel\EasyDecisionServiceProvider',
    false
);
