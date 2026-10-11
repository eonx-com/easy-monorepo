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
            // Driver errors embed row/column values in the message (format varies per engine); keep only
            // the value-free skeleton instead of matching every format
            $reduce = static function (Report $report): void {
                $error = $report->getOriginalError();

                if ($error instanceof DriverException) {
                    $report->setMessage(
                        \sprintf('SQLSTATE[%s] %s', $error->getSQLState() ?? 'unknown', $error::class)
                    );
                }
            };

            $reduce($report);

            $reducePreviousChain = Closure::bind(
                static function (Report $report) use ($reduce): void {
                    $previous = $report->previous ?? null;

                    while ($previous instanceof Report) {
                        $reduce($previous);
                        $previous = $previous->previous ?? null;
                    }
                },
                null,
                Report::class
            );

            $reducePreviousChain($report);
        });
    }
}
