<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Configurator;

use Bugsnag\Client;
use Bugsnag\Report;
use Closure;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Throwable;

final class DatabaseErrorScrubberClientConfigurator extends AbstractClientConfigurator
{
    /**
     * Shorter messages are not replaced in the metadata, so a common string cannot be replaced by accident
     */
    private const int MESSAGE_LENGTH_MIN = 16;

    public function __construct()
    {
        // After the configurators that set the message or copy it into the metadata (e.g. EasyErrorHandler's
        // ErrorDetailsClientConfigurator, priority 0), before SensitiveDataSanitizerClientConfigurator (PHP_INT_MAX)
        parent::__construct(\PHP_INT_MAX - 1);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag->registerCallback(static function (Report $report): void {
            // Driver errors embed values in the message (format varies per engine) and the native PDO/mysqli
            // exception below repeats it: from the first driver exception down, keep only SQLSTATE + class
            $reduceChain = Closure::bind(
                static function (Report $report): array {
                    $reducedMessages = [];
                    $sqlState = null;

                    for ($current = $report; $current instanceof Report; $current = $current->previous ?? null) {
                        $error = $current->getOriginalError();

                        if ($error instanceof DriverException) {
                            $sqlState = $error->getSQLState() ?? 'unknown';
                        }

                        if ($sqlState !== null) {
                            $reducedMessage = \sprintf('SQLSTATE[%s] %s', $sqlState, \get_debug_type($error));
                            $current->setMessage($reducedMessage);

                            if ($error instanceof Throwable) {
                                $reducedMessages[] = [$error->getMessage(), $reducedMessage];
                            }
                        }
                    }

                    return $reducedMessages;
                },
                null,
                Report::class
            );

            self::reduceMetaData($report, $reduceChain($report));
        });
    }

    /**
     * Other configurators copy the messages into the metadata (e.g. "error.message", "error.previous_1.message")
     *
     * @param list<array{string, string}> $reducedMessages Original message and its reduced form
     */
    private static function reduceMetaData(Report $report, array $reducedMessages): void
    {
        $search = [];
        $replace = [];

        // The chain goes from the top, so a wrapper message ("An exception occurred in the driver: <message>")
        // is replaced before the driver message it contains
        foreach ($reducedMessages as [$message, $reducedMessage]) {
            if (\strlen($message) >= self::MESSAGE_LENGTH_MIN) {
                $search[] = $message;
                $replace[] = $reducedMessage;
            }
        }

        if ($search === []) {
            return;
        }

        $metaData = $report->getMetaData();

        \array_walk_recursive($metaData, static function (mixed &$value) use ($search, $replace): void {
            if (\is_string($value)) {
                $value = \str_replace($search, $replace, $value);
            }
        });

        $report->setMetaData($metaData, false);
    }
}
