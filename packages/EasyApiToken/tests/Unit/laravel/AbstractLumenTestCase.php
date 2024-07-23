<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Laravel;

use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;
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
        $this->app->boot();

        return $this->app;
    }
}
