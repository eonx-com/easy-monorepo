<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Tests;

use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyRequestId\DefaultResolver;
use EonX\EasyRequestId\Interfaces\FallbackResolverInterface;
use EonX\EasyRequestId\Interfaces\ResolverInterface;
use EonX\EasyRequestId\RequestIdService;
use EonX\EasyRequestId\UuidV4FallbackResolver;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

final class RequestIdServiceTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestGetIds(): iterable
    {
        yield 'Default fallback to UUID' => [
            new Request(),
            [],
            [],
            null,
            null,
            static function (string $requestId, string $correlationId): void {
                self::assertTrue(Uuid::isValid($requestId));
                self::assertTrue(Uuid::isValid($correlationId));
            },
        ];

        $defaultResolver = new DefaultResolver();

        yield 'Default resolver with default values' => [
            $this->getRequestWithHeaders([
                ResolverInterface::DEFAULT_REQUEST_ID_HEADER => 'request-id',
                ResolverInterface::DEFAULT_CORRELATION_ID_HEADER => 'correlation-id',
            ]),
            [$defaultResolver],
            [$defaultResolver],
            'request-id',
            'correlation-id',
        ];
    }

    /**
     * @param mixed[] $requestIdResolvers
     * @param mixed[] $correlationIdResolvers
     *
     * @dataProvider providerTestGetIds
     */
    public function testGetIds(
        Request $request,
        array $requestIdResolvers,
        array $correlationIdResolvers,
        ?string $requestId = null,
        ?string $correlationId = null,
        ?callable $assert = null,
        ?FallbackResolverInterface $fallbackResolver = null
    ): void {
        $fallbackResolver = $fallbackResolver ?? $this->defaultFallbackResolver();
        $service = new RequestIdService($request, $requestIdResolvers, $correlationIdResolvers, $fallbackResolver);

        // For caching coverage
        $service->getCorrelationId();
        $service->getRequestId();

        if ($requestId !== null) {
            self::assertEquals($requestId, $service->getRequestId());
        }

        if ($correlationId !== null) {
            self::assertEquals($correlationId, $service->getCorrelationId());
        }

        if ($assert !== null) {
            $assert($service->getRequestId(), $service->getCorrelationId());
        }
    }

    private function defaultFallbackResolver(): FallbackResolverInterface
    {
        return new UuidV4FallbackResolver((new RandomGenerator())->setUuidV4Generator(new RamseyUuidV4Generator()));
    }
}
