<?php
declare(strict_types=1);

namespace EonX\EasyTest\Monolog\Trait;

use EonX\EasyTest\Monolog\Processor\LogsCollectorProcessor;
use Monolog\Level;
use PHPUnit\Framework\Attributes\Before;

/**
 * Assertions on log records collected by {@see LogsCollectorProcessor} when using symfony/monolog-bundle.
 *
 * @mixin \EonX\EasyTest\Common\Trait\ContainerServiceTrait
 * @mixin \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
 */
trait MonologTrait
{
    #[Before]
    public function setUpMonolog(): void
    {
        LogsCollectorProcessor::reset();
    }

    protected static function assertLoggerHas(Level $level, string $message, ?array $context = null): void
    {
        $records = [];
        foreach (LogsCollectorProcessor::getRecords() as $record) {
            if ($record->level === $level && $record->message === $message) {
                $records[] = $record;
            }
        }

        $hasRecord = \count($records) > 0;

        if ($context !== null) {
            $hasRecord = false;

            foreach ($records as $record) {
                if ($record->context === $context) {
                    $hasRecord = true;

                    break;
                }
            }
        }

        if ($hasRecord === false) {
            self::fail(\sprintf(
                'Log message not found.' . \PHP_EOL
                . 'Level: %s' . \PHP_EOL
                . 'Message: %s' . \PHP_EOL
                . 'Context: ' . \PHP_EOL . '%s' . \PHP_EOL
                . 'Existing records:' . \PHP_EOL . '%s',
                $level->getName(),
                $message,
                \var_export($context, true),
                \var_export(LogsCollectorProcessor::getRecords(), true)
            ));
        }
    }

    protected static function assertLoggerHasInfo(string $message, ?array $context = null): void
    {
        self::assertLoggerHas(Level::Info, $message, $context);
    }

    protected static function assertLoggerHasRecordMatchesRegularExpression(string $pattern): void
    {
        foreach (LogsCollectorProcessor::getRecords() as $record) {
            if (\preg_match($pattern, $record->message) === 1) {
                return;
            }
        }

        self::fail(\sprintf('No log record message matches the "%s" pattern.', $pattern));
    }

    protected static function assertLoggerHasWarning(string $message, ?array $context = null): void
    {
        self::assertLoggerHas(Level::Warning, $message, $context);
    }
}
