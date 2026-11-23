<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Stub\Configurator;

use Bugsnag\Client;
use Bugsnag\Middleware\CallbackBridge;
use Bugsnag\Report;
use EonX\EasyBugsnag\Common\Configurator\AbstractClientConfigurator;
use Throwable;

/**
 * Does what EasyErrorHandler's ErrorDetailsClientConfigurator does: sets the message of the original throwable and
 * copies the messages of the chain into the "error" metadata
 */
final class ErrorDetailsClientConfiguratorStub extends AbstractClientConfigurator
{
    public function __construct()
    {
        // ErrorDetailsClientConfigurator has priority 0 and runs after other priority 0 configurators of EasyBugsnag
        // because EasyErrorHandlerBundle comes after EasyBugsnagBundle in bundles.php. Here the stub is an app service,
        // which comes before the bundle services, so priority 1 keeps the same order
        parent::__construct(1);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag
            ->getPipeline()
            ->pipe(new CallbackBridge(static function (Report $report): void {
                $throwable = $report->getOriginalError();

                if ($throwable instanceof Throwable === false) {
                    return;
                }

                $details = [
                    'message' => $throwable->getMessage(),
                ];

                for ($depth = 1, $previous = $throwable->getPrevious(); $previous !== null; $depth++) {
                    $details[\sprintf('previous_%d', $depth)] = [
                        'message' => $previous->getMessage(),
                    ];
                    $previous = $previous->getPrevious();
                }

                $report->setMessage($throwable->getMessage());
                $report->addMetaData([
                    'error' => $details,
                ]);
            }));
    }
}
