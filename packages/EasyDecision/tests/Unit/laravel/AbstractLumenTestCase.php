<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Tests\Unit\Bundle;

use EonX\EasyDecision\Bundle\Enum\ConfigTag;
use EonX\EasyDecision\Factory\DecisionFactoryInterface;
use EonX\EasyDecision\Laravel\EasyDecisionServiceProvider;
use EonX\EasyDecision\Tests\Stub\Configurator\RulesConfiguratorStub;
use EonX\EasyDecision\Tests\Unit\AbstractUnitTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractUnitTestCase
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
        $this->app->tag(RulesConfiguratorStub::class, [ConfigTag::DecisionConfigurator->value]);

        return $this->app;
    }

    protected function getDecisionFactory(): DecisionFactoryInterface
    {
        return $this->getApplication()
            ->make(DecisionFactoryInterface::class);
    }
}
