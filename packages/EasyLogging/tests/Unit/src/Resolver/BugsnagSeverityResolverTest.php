<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Resolver;

use EonX\EasyLogging\Enum\BugsnagSeverity;
use EonX\EasyLogging\Resolver\BugsnagSeverityResolver;
use EonX\EasyLogging\Tests\Unit\AbstractSymfonyTestCase;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;

final class BugsnagSeverityResolverTest extends AbstractSymfonyTestCase
{
    /**
     * @see testItSucceeds
     */
    public static function provideLevels(): iterable
    {
        yield 'debug' => [Logger::DEBUG, BugsnagSeverity::Info];
        yield 'info' => [Logger::INFO, BugsnagSeverity::Info];
        yield 'notice' => [Logger::NOTICE, BugsnagSeverity::Info];
        yield 'warning' => [Logger::WARNING, BugsnagSeverity::Info];
        yield 'error' => [Logger::ERROR, BugsnagSeverity::Warning];
        yield 'critical' => [Logger::CRITICAL, BugsnagSeverity::Error];
        yield 'alert' => [Logger::ALERT, BugsnagSeverity::Error];
        yield 'emergency' => [Logger::EMERGENCY, BugsnagSeverity::Error];
    }

    #[DataProvider('provideLevels')]
    public function testItSucceeds(int $level, BugsnagSeverity $expected): void
    {
        $sut = new BugsnagSeverityResolver();

        self::assertSame($expected, $sut->resolve($level));
    }
}
