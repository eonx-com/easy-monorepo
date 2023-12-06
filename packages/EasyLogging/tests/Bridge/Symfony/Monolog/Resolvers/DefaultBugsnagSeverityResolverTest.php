<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Bridge\Symfony\Monolog\Resolvers;

use EonX\EasyLogging\Bridge\Symfony\Monolog\Resolvers\BugsnagSeverityResolver;
use EonX\EasyLogging\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;

final class DefaultBugsnagSeverityResolverTest extends AbstractSymfonyTestCase
{
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
