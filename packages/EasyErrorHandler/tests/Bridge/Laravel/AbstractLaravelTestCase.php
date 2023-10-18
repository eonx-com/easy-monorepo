<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Laravel;

use Bugsnag\Client;
use EonX\EasyErrorHandler\Bridge\Laravel\Provider\EasyErrorHandlerServiceProvider;
use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use EonX\EasyErrorHandler\Tests\Stubs\BugsnagClientStub;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractTestCase
{
    private ?Application $app = null;

    protected function getApplication(?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);

        $config ??= [];
        $config['easy-error-handler']['response'] = [
            'code' => 'custom_code',
            'exception' => 'custom_exception',
            'extended_exception_keys' => [
                'class' => 'custom_class',
                'file' => 'custom_file',
                'line' => 'custom_line',
                'message' => 'custom_message',
                'trace' => 'custom_trace',
            ],
            'message' => 'custom_message',
            'sub_code' => 'custom_sub_code',
            'time' => 'custom_time',
            'violations' => 'custom_violations',
        ];

        \config($config);

        $this->app->register(EasyErrorHandlerServiceProvider::class);
        $this->app->instance(Client::class, new BugsnagClientStub());
        $this->app->boot();

        return $this->app;
    }
}
