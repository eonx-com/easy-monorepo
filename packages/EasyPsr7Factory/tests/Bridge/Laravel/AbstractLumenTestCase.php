<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Tests\Bridge\Laravel;

use EonX\EasyPsr7Factory\Bridge\Laravel\EasyPsr7FactoryServiceProvider;
use EonX\EasySecurity\Tests\AbstractTestCase;
use Illuminate\Http\Request as IlluminateRequest;
use Laravel\Lumen\Application;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractLumenTestCase extends AbstractTestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    protected function getApplication(?Request $request = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $app = new Application(__DIR__);
        $app->register(EasyPsr7FactoryServiceProvider::class);

        if ($request !== null) {
            $app->instance(IlluminateRequest::class, $request);
        }

        return $this->app = $app;
    }
}
