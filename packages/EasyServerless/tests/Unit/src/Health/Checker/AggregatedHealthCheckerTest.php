<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\src\Health\Checker;

use EonX\EasyServerless\Health\Checker\AggregatedHealthChecker;
use EonX\EasyServerless\Health\Checker\SanityChecker;
use EonX\EasyServerless\Tests\Stub\Health\Checker\CheckerStub;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use RuntimeException;

final class AggregatedHealthCheckerTest extends AbstractUnitTestCase
{
    public function testCheckSucceeds(): void
    {
        $checkers = [
            new SanityChecker(),
            new CheckerStub(
                name: 'stub_not_healthy',
                isHealthy: false
            ),
            new CheckerStub(
                name: 'stub_throwable',
                throwable: new RuntimeException('__error__')
            ),
        ];
        $aggregatedChecker = new AggregatedHealthChecker($checkers);

        $results = $aggregatedChecker->check();

        self::assertCount(3, $results);
        self::assertTrue($results['sanity']->isHealthy());
        self::assertFalse($results['stub_not_healthy']->isHealthy());
        self::assertFalse($results['stub_throwable']->isHealthy());
        self::assertSame('__error__', $results['stub_throwable']->getReason());
    }
}
