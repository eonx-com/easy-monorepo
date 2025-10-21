<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\src\Health\Controller;

use EonX\EasyServerless\Health\Checker\AggregatedHealthChecker;
use EonX\EasyServerless\Health\Checker\SanityChecker;
use EonX\EasyServerless\Health\Controller\HealthCheckController;
use EonX\EasyServerless\Tests\Stub\Health\Checker\CheckerStub;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class HealthCheckControllerTest extends AbstractUnitTestCase
{
    /**
     * @see testInvokeSucceeds
     */
    public static function provideCheckersAndStatusCodes(): iterable
    {
        yield 'Healthy' => [
            'checkers' => [
                new SanityChecker(),
            ],
            'statusCode' => 200,
        ];

        yield 'Not Healthy' => [
            'checkers' => [
                new CheckerStub(name: 'stub_not_healthy', isHealthy: false),
            ],
            'statusCode' => 500,
        ];
    }

    #[DataProvider('provideCheckersAndStatusCodes')]
    public function testInvokeSucceeds(array $checkers, int $statusCode): void
    {
        $aggregatedChecker = new AggregatedHealthChecker($checkers);
        $controller = new HealthCheckController($aggregatedChecker);

        $response = $controller();

        self::assertSame($statusCode, $response->getStatusCode());
    }
}
