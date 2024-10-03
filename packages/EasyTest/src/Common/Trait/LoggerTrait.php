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
        self::assertTrue(
            self::getLoggerService()->hasInfo([
                'context' => $context,
                'message' => $message,
            ])
        );
    }

    protected static function assertLoggerHasRecordMatchesRegularExpression(string $pattern): void
    {
        foreach (self::getLoggerService()->getRecords() as $record) {
            if (\preg_match($pattern, $record['message'] ?? '')) {
                return;
            }
        }
        self::assertTrue(false, "Log message with the '$pattern' not found.");
    }

    protected static function assertLoggerHasWarning(string $message, ?array $context = null): void
    {
        self::assertTrue(
            self::getLoggerService()->hasWarning([
                'context' => $context,
                'message' => $message,
            ])
        );
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
}
