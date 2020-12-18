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
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    protected function getApplication(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $app = new Application(__DIR__);
        $app->register(EasyDecisionServiceProvider::class);
        $app->boot();

        $app->singleton(RulesConfiguratorStub::class);
        $app->tag(RulesConfiguratorStub::class, [BridgeConstantsInterface::TAG_DECISION_CONFIGURATOR]);

        return $this->app = $app;
    }

    protected function getDecisionFactory(): DecisionFactoryInterface
    {
        return $this->getApplication()
            ->make(DecisionFactoryInterface::class);
    }
}
