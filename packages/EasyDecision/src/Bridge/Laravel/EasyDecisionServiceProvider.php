<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use EonX\EasyDecision\Bridge\Common\DecisionFactory as BridgeDecisionFactory;
use EonX\EasyDecision\Bridge\Common\ExpressionLanguageConfigFactory;
use EonX\EasyDecision\Bridge\Common\Interfaces\DecisionFactoryInterface;
use EonX\EasyDecision\Bridge\Common\Interfaces\ExpressionLanguageConfigFactoryInterface;
use EonX\EasyDecision\Decisions\DecisionFactory as BaseDecisionFactory;
use EonX\EasyDecision\Expressions\ExpressionFunctionFactory;
use EonX\EasyDecision\Expressions\ExpressionLanguageFactory;
use EonX\EasyDecision\Interfaces\ExpressionLanguageRuleFactoryInterface;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionFunctionFactoryInterface;
use EonX\EasyDecision\Interfaces\Expressions\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Rules\ExpressionLanguageRuleFactory;

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
