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
                    $reportsAbove = [];
                    $sqlState = null;

                    for ($current = $report; $current instanceof Report; $current = $current->previous ?? null) {
                        $error = $current->getOriginalError();

                        if ($error instanceof DriverException) {
                            $sqlState = $error->getSQLState() ?? 'unknown';
                        }

                        if ($sqlState === null) {
                            $reportsAbove[] = $current;

                            continue;
                        }

                        // The short class name has no backslashes, so the reduced message can replace a copy of the
                        // message inside a JSON string in the metadata and the JSON stays valid
                        $className = (string)\strrchr('\\' . \get_debug_type($error), '\\');
                        $reducedMessage = \sprintf('SQLSTATE[%s] %s', $sqlState, \substr($className, 1));
                        $current->setMessage($reducedMessage);

                        if ($error instanceof Throwable) {
                            $reducedMessages[] = [$error->getMessage(), $reducedMessage, $sqlState];
                        }
                    }

                    return [$reducedMessages, $reportsAbove];
                },
                null,
                Report::class
            );

            [$reducedMessages, $reportsAbove] = $reduceChain($report);
            [$search, $replace] = self::resolveReplacements($reducedMessages);

            if ($search === []) {
                return;
            }

            // The application can put a DB message into its own message, e.g. "Checkout failed: <message>"
            foreach ($reportsAbove as $reportAbove) {
                $reportAbove->setMessage(\str_replace($search, $replace, (string)$reportAbove->getMessage()));
            }

            // Other configurators copy the messages into the metadata (e.g. "error.message", "error.previous_1.message")
            $metaData = $report->getMetaData();

            \array_walk_recursive($metaData, static function (mixed &$value) use ($search, $replace): void {
                if (\is_string($value)) {
                    $value = \str_replace($search, $replace, $value);
                }
            });

            $report->setMetaData($metaData, false);
        });
    }

    /**
     * @param list<array{string, string, string}> $reducedMessages Original message, its reduced form and SQLSTATE
     *
     * @return array{list<string>, array<int, string>}
     */
    private static function resolveReplacements(array $reducedMessages): array
    {
        $search = [];
        $replace = [];

        // The chain goes from the top, so a wrapper message ("An exception occurred in the driver: <message>")
        // is replaced before the driver message it contains
        foreach ($reducedMessages as [$message, $reducedMessage, $sqlState]) {
            if (\strlen($message) < self::MESSAGE_LENGTH_MIN) {
                continue;
            }

            $index = \array_search($message, $search, true);

            if ($index === false) {
                $search[] = $message;
                $replace[] = $reducedMessage;

                continue;
            }

            // Two exceptions with the same message (a PDO driver exception and its PDOException): a copy cannot be
            // traced to one of them, so it gets no class
            if ($replace[$index] !== $reducedMessage) {
                $replace[$index] = \sprintf('SQLSTATE[%s]', $sqlState);
            }
        }

        return [$search, $replace];
    }
}
