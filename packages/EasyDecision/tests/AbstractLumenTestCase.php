<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests;

use EonX\EasyDecision\Bridge\BridgeConstantsInterface;
use EonX\EasyDecision\Bridge\Laravel\EasyDecisionServiceProvider;
use EonX\EasyDecision\Interfaces\DecisionFactoryInterface;
use EonX\EasyDecision\Tests\Stubs\RulesConfiguratorStub;
use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractTestCase
{
    private ?Application $app = null;

    protected function getApplication(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->register(EasyDecisionServiceProvider::class);
        $this->app->boot();

        $this->app->singleton(RulesConfiguratorStub::class);
        $this->app->tag(RulesConfiguratorStub::class, [BridgeConstantsInterface::TAG_DECISION_CONFIGURATOR]);

        return $this->app;
    }

    protected function getDecisionFactory(): DecisionFactoryInterface
    {
        return $this->getApplication()
            ->make(DecisionFactoryInterface::class);
    }
}
