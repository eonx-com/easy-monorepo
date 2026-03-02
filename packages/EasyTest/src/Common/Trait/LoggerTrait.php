<?php
declare(strict_types=1);

namespace EonX\EasyTest\Common\Trait;

use EonX\EasyLogging\Logger\LazyLogger;
use EonX\EasyTest\Monolog\Logger\LoggerStub;
use Psr\Log\LoggerInterface;

/**
 * @mixin \EonX\EasyTest\Common\Trait\ContainerServiceTrait
 * @mixin \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
 */
trait LoggerTrait
{
    protected static function assertLoggerHasInfo(string $message, ?array $context = null): void
    {
        $logger = self::getLoggerService();

        if ($logger->hasInfo(['context' => $context, 'message' => $message]) === false) {
            self::fail(self::createNotFoundLogErrorMessage($message, 'info', $context, $logger->getRecords()));
        }
    }

    protected static function assertLoggerHasRecordMatchesRegularExpression(string $pattern): void
    {
        $records = self::getLoggerService()->getRecords();
        foreach ($records as $record) {
            if (\preg_match($pattern, $record['message'] ?? '')) {
                return;
            }
        }

        $error = 'No log record message matches the regular expression pattern.' . \PHP_EOL;
        $error .= 'Pattern: ' . $pattern . \PHP_EOL;
        $error .= 'All log records: ' . \PHP_EOL;
        $error .= \json_encode($records, \JSON_PRETTY_PRINT);

        self::fail($error);
    }

    protected static function assertLoggerHasWarning(string $message, ?array $context = null): void
    {
        $logger = self::getLoggerService();

        if ($logger->hasWarning(['context' => $context, 'message' => $message]) === false) {
            self::fail(self::createNotFoundLogErrorMessage($message, 'warning', $context, $logger->getRecords()));
        }
    }

    protected static function getLoggerService(): LoggerStub
    {
        $loggerService = self::getService(LoggerInterface::class);

        if ($loggerService instanceof LazyLogger) {
            $loggerService = $loggerService->getLogger();
        }

        /** @var \EonX\EasyTest\Monolog\Logger\LoggerStub $logger */
        $logger = $loggerService;

        return $logger;
    }

    private static function createNotFoundLogErrorMessage(
        string $message,
        string $level,
        ?array $context = null,
        array $records = [],
    ): string {
        $error = 'The "' . \ucfirst($level) . '" log message was not found.' . \PHP_EOL;
        $error .= 'Message: ' . $message . \PHP_EOL;
        $error .= 'Context: ' . \json_encode($context, \JSON_PRETTY_PRINT) . \PHP_EOL;
        $error .= 'All log records: ' . \PHP_EOL;
        $error .= \json_encode($records, \JSON_PRETTY_PRINT);

        return $error;
    }
}
