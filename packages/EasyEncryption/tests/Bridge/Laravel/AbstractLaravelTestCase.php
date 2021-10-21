<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Bridge\Laravel;

use EonX\EasyEncryption\Bridge\Laravel\Provider\EasyEncryptionServiceProvider;
use EonX\EasyEncryption\Tests\AbstractTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractTestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    /**
     * @param null|mixed[] $config
     */
    protected function getApplication(?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $app = new Application(__DIR__);

        if ($config !== null) {
            \config($config);
        }

        $app->register(EasyEncryptionServiceProvider::class);

        return $this->app = $app;
    }

    protected function setAppKey(string $appKey): void
    {
        \putenv(\sprintf('APP_KEY=%s', $appKey));
    }
}
