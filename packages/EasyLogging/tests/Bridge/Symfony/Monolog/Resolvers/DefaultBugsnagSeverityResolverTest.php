<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Bridge\Symfony\Monolog\Resolvers;

use EonX\EasyLogging\Bridge\Symfony\Monolog\Resolvers\DefaultBugsnagSeverityResolver;
use EonX\EasyLogging\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;

final class DefaultBugsnagSeverityResolverTest extends AbstractSymfonyTestCase
{
    public static function provideLevels(): iterable
    {
        yield 'debug' => [Logger::DEBUG, 'warning'];
        yield 'info' => [Logger::INFO, 'warning'];
        yield 'notice' => [Logger::NOTICE, 'warning'];
        yield 'warning' => [Logger::WARNING, 'warning'];
        yield 'error' => [Logger::ERROR, 'error'];
        yield 'critical' => [Logger::CRITICAL, 'error'];
        yield 'alert' => [Logger::ALERT, 'error'];
        yield 'emergency' => [Logger::EMERGENCY, 'error'];
    }

    #[DataProvider('provideLevels')]
    public function testItSucceeds(int $level, string $expected): void
    {
        $sut = new DefaultBugsnagSeverityResolver();

        self::assertSame($expected, $sut->resolve($level));
    }
}
