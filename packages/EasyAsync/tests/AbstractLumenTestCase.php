<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests;

use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractTestCase
{
    protected function createApplication(): Application
    {
        return new Application();
    }
}
