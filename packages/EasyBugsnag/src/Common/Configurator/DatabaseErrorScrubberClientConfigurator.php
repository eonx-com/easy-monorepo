<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Configurator;

use Bugsnag\Client;
use Bugsnag\Report;
use Closure;
use Doctrine\DBAL\Driver\Exception as DriverException;

final class DatabaseErrorScrubberClientConfigurator extends AbstractClientConfigurator
{
    public function configure(Client $bugsnag): void
    {
        $bugsnag->registerCallback(static function (Report $report): void {
            // Driver errors embed values in the message (format varies per engine) and the native PDO/mysqli
            // exception below repeats it: from the first driver exception down, keep only SQLSTATE + class
            $reduceChain = Closure::bind(
                static function (Report $report): void {
                    $sqlState = null;

                    for ($current = $report; $current instanceof Report; $current = $current->previous ?? null) {
                        $error = $current->getOriginalError();

                        if ($error instanceof DriverException) {
                            $sqlState = $error->getSQLState() ?? 'unknown';
                        }

                        if ($sqlState !== null) {
                            $current->setMessage(\sprintf('SQLSTATE[%s] %s', $sqlState, \get_debug_type($error)));
                        }
                    }
                },
                null,
                Report::class
            );

            $reduceChain($report);
        });
    }
}
