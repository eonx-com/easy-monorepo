<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Resolver;

use EonX\EasyLogging\Enum\BugsnagSeverity;
use EonX\EasyLogging\Resolver\BugsnagSeverityResolver;
use EonX\EasyLogging\Tests\Unit\AbstractSymfonyTestCase;
use Monolog\Level;
use PHPUnit\Framework\Attributes\DataProvider;

final class BugsnagSeverityResolverTest extends AbstractSymfonyTestCase
{
    /**
     * @see testItSucceeds
     */
    public static function provideLevels(): iterable
    {
        yield 'debug' => [Level::Debug, BugsnagSeverity::Info];
        yield 'info' => [Level::Info, BugsnagSeverity::Info];
        yield 'notice' => [Level::Notice, BugsnagSeverity::Info];
        yield 'warning' => [Level::Warning, BugsnagSeverity::Info];
        yield 'error' => [Level::Error, BugsnagSeverity::Warning];
        yield 'critical' => [Level::Critical, BugsnagSeverity::Error];
        yield 'alert' => [Level::Alert, BugsnagSeverity::Error];
        yield 'emergency' => [Level::Emergency, BugsnagSeverity::Error];
    }

    #[DataProvider('provideLevels')]
    public function testItSucceeds(Level $level, BugsnagSeverity $expected): void
    {
        $sut = new BugsnagSeverityResolver();

        self::assertSame($expected, $sut->resolve($level));
    }
}
