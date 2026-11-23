<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Configurator;

use Bugsnag\Client;
use Bugsnag\Report;
use Closure;
use DateTimeInterface;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Throwable;
use UnitEnum;

final class DatabaseErrorScrubberClientConfigurator extends AbstractClientConfigurator
{
    private const array JSON_ENCODE_FLAGS = [
        0,
        \JSON_UNESCAPED_SLASHES,
        \JSON_UNESCAPED_UNICODE,
        \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE,
    ];

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
                        $className = (string) \strrchr('\\' . \get_debug_type($error), '\\');
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
            $replacements = self::resolveReplacements($reducedMessages);

            if ($replacements === []) {
                return;
            }

            // The application can put a DB message into its own message, e.g. "Checkout failed: <message>"
            foreach ($reportsAbove as $reportAbove) {
                $reportAbove->setMessage(\strtr((string) $reportAbove->getMessage(), $replacements));
            }

            // Other configurators copy the messages into the metadata (e.g. "error.message", "error.previous_1.message")
            /** @var array<array> $metaData */
            $metaData = self::reduceValue($report->getMetaData(), $replacements);
            $report->setMetaData($metaData, false);
        });
    }

    /**
     * Objects and JSON strings are read the way they reach the report: Bugsnag and the sensitive data sanitizer turn
     * an object into the array of its JSON form, and the sanitizer re-encodes a JSON string. A message inside JSON that
     * is embedded in text is replaced only in the JSON-escaped forms of JSON_ENCODE_FLAGS
     *
     * @param array<string, string> $replacements
     */
    private static function reduceValue(mixed $value, array $replacements): mixed
    {
        if (\is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = self::reduceValue($item, $replacements);
            }

            return $value;
        }

        if (\is_object($value)) {
            if ($value instanceof DateTimeInterface || $value instanceof Throwable || $value instanceof UnitEnum) {
                return $value;
            }

            $data = (array) \json_decode((string) \json_encode($value), true);
            $reducedData = self::reduceValue($data, $replacements);

            return $reducedData === $data ? $value : $reducedData;
        }

        if (\is_string($value) === false) {
            return $value;
        }

        $data = \json_decode($value, true, flags: \JSON_BIGINT_AS_STRING);

        if (\is_array($data)) {
            $reducedData = self::reduceValue($data, $replacements);

            return $reducedData === $data
                ? $value
                : (string) \json_encode($reducedData, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
        }

        // One pass that takes the longest match at each position: a JSON-escaped form wins over the raw message it
        // starts with, so a backslash pair is never split
        return \strtr($value, $replacements);
    }

    /**
     * @param list<array{string, string, string}> $reducedMessages Original message, its reduced form and SQLSTATE
     *
     * @return array<string, string> Reduced form by original message
     */
    private static function resolveReplacements(array $reducedMessages): array
    {
        $replacements = [];

        foreach ($reducedMessages as [$message, $reducedMessage, $sqlState]) {
            if (\strlen($message) < self::MESSAGE_LENGTH_MIN) {
                continue;
            }

            // Two exceptions with the same message (a PDO driver exception and its PDOException): a copy cannot be
            // traced to one of them, so it gets no class
            $replacements[$message] = isset($replacements[$message]) && $replacements[$message] !== $reducedMessage
                ? \sprintf('SQLSTATE[%s]', $sqlState)
                : $reducedMessage;
        }

        // A copy can also sit JSON-encoded in a string (newlines, quotes and backslashes escaped). The reduced message has
        // no characters that JSON escapes, so it replaces the encoded form as it is
        foreach ($replacements as $message => $reducedMessage) {
            foreach (self::JSON_ENCODE_FLAGS as $flags) {
                // False for a message with invalid UTF-8, which JSON cannot hold
                $encodedMessage = \json_encode((string) $message, $flags);

                if ($encodedMessage !== false) {
                    $replacements[\substr($encodedMessage, 1, -1)] ??= $reducedMessage;
                }
            }
        }

        return $replacements;
    }
}
