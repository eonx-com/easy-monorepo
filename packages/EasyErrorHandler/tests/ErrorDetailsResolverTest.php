<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests;

use EonX\EasyErrorHandler\ErrorDetailsResolver;
use EonX\EasyErrorHandler\Tests\Stubs\TranslatorStub;
use Psr\Log\NullLogger;

final class ErrorDetailsResolverTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testResolveExtendedDetails
     */
    public function providerTestResolveExtendedDetails(): iterable
    {
        yield 'simple' => [new \Exception()];

        yield 'max depth 0 so no previous' => [
            new \Exception('message', 0, new \Exception()),
            static function (array $details): void {
                self::assertNull($details['previous_1'] ?? null);
            },
            0,
        ];

        yield 'max depth -1 so infinite' => [
            $this->createExceptionChain(100),
            static function (array $details): void {
                self::assertNotNull($details['previous_1'] ?? null);
            },
            -1,
        ];

        yield 'all previous in root level' => [
            $this->createExceptionChain(2),
            static function (array $details): void {
                self::assertNotNull($details['previous_1'] ?? null);
                self::assertNotNull($details['previous_2'] ?? null);
            },
        ];
    }

    /**
     * @dataProvider providerTestResolveExtendedDetails
     */
    public function testResolveExtendedDetails(
        \Throwable $throwable,
        ?callable $test = null,
        ?int $maxDepth = null
    ): void {
        $errorDetailsResolver = new ErrorDetailsResolver(new NullLogger(), new TranslatorStub());
        $details = $errorDetailsResolver->resolveExtendedDetails($throwable, $maxDepth);

        self::assertEquals($throwable->getCode(), $details['code']);
        self::assertEquals(\get_class($throwable), $details['class']);
        self::assertEquals($throwable->getFile(), $details['file']);
        self::assertEquals($throwable->getLine(), $details['line']);
        self::assertEquals($throwable->getMessage(), $details['message']);

        if ($test !== null) {
            $test($details);
        }
    }

    private function createExceptionChain(int $max, ?int $current = null, ?\Throwable $previous = null): \Throwable
    {
        $current = $current ?? 0;
        $previous = $previous ?? new \Exception();

        if ($max === $current) {
            return $previous;
        }

        return $this->createExceptionChain($max, $current + 1, new \Exception('message', 0, $previous));
    }
}
