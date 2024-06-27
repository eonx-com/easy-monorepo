<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Resolver;

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
        yield 'debug' => [Logger::DEBUG, 'info'];
        yield 'info' => [Logger::INFO, 'info'];
        yield 'notice' => [Logger::NOTICE, 'info'];
        yield 'warning' => [Logger::WARNING, 'info'];
        yield 'error' => [Logger::ERROR, 'warning'];
        yield 'critical' => [Logger::CRITICAL, 'error'];
        yield 'alert' => [Logger::ALERT, 'error'];
        yield 'emergency' => [Logger::EMERGENCY, 'error'];
    }

    #[DataProvider('provideLevels')]
    public function testItSucceeds(int $level, string $expected): void
    {
        $sut = new BugsnagSeverityResolver();

        self::assertSame($expected, $sut->resolve($level));
    }
}
