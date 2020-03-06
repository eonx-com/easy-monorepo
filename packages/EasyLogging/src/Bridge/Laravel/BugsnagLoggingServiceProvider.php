<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Laravel;

use EonX\EasyLogging\Bugsnag\BugsnagLogClient;
use EonX\EasyLogging\Doctrine\ExternalLogger;
use EonX\EasyLogging\Interfaces\ExternalLogClientInterface;
use EonX\EasyLogging\Interfaces\SqlLoggerInterface;
use Illuminate\Support\ServiceProvider;

final class BugsnagLoggingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register bugsnag dependencies.
        $this->app->singleton(ExternalLogClientInterface::class, function (): ExternalLogClientInterface {
            return new BugsnagLogClient($this->app->get('bugsnag'));
        });

        $this->app->singleton(SqlLoggerInterface::class, function (): SqlLoggerInterface {
            return new ExternalLogger(
                $this->app->get(ExternalLogClientInterface::class),
                \filter_var(\config('bugsnag.bindings', false), \FILTER_VALIDATE_BOOLEAN)
            );
        });
    }
}
