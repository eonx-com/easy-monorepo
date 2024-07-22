<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Laravel;

use EonX\EasyEncryption\Laravel\EasyEncryptionServiceProvider;
use EonX\EasyEncryption\Tests\Unit\AbstractUnitTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractUnitTestCase
{
    private ?Application $app = null;

    protected function getApplication(?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);

        if ($config !== null) {
            \config($config);
        }

        $this->app->register(EasyEncryptionServiceProvider::class);

        return $this->app;
    }

    protected function setAppKey(string $appKey): void
    {
        \putenv(\sprintf('APP_KEY=%s', $appKey));
    }
}
