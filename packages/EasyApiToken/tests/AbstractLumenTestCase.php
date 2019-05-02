<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tests;

use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractTestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    /**
     * Get lumen application.
     *
     * @return \Laravel\Lumen\Application
     */
    protected function getApplication(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $app = new Application(__DIR__);
        $app->boot();

        return $this->app = $app;
    }
}

\class_alias(
    AbstractLumenTestCase::class,
    'StepTheFkUp\EasyApiToken\Tests\AbstractLumenTestCase',
    false
);
