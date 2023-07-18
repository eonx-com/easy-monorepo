<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Tests;

use EonX\EasyRandom\RandomGenerator;
use EonX\EasyRandom\UuidV4\RamseyUuidV4Generator;
use EonX\EasyRequestId\Interfaces\FallbackResolverInterface;
use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use EonX\EasyRequestId\RequestIdService;
use EonX\EasyRequestId\Resolvers\HttpFoundationRequestResolver;
use EonX\EasyRequestId\UuidV4FallbackResolver;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

final class RequestIdServiceTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testGetIds
     */
    public static function providerTestGetIds(): iterable
    {
        yield 'Default fallback to UUID' => [
            new Request(),
            null,
            null,
            static function (string $requestId, string $correlationId): void {
                self::assertTrue(Uuid::isValid($requestId));
                self::assertTrue(Uuid::isValid($correlationId));
            },
        ];

        yield 'Default resolver with default values' => [
            self::getRequestWithHeaders([
                RequestIdServiceInterface::DEFAULT_HTTP_HEADER_CORRELATION_ID => 'correlation-id',
                RequestIdServiceInterface::DEFAULT_HTTP_HEADER_REQUEST_ID => 'request-id',
            ]),
            'request-id',
            'correlation-id',
        ];
    }

    /**
     * @dataProvider providerTestGetIds
     */
    public function testGetIds(
        Request $request,
        ?string $requestId = null,
        ?string $correlationId = null,
        ?callable $assert = null,
        ?FallbackResolverInterface $fallbackResolver = null,
    ): void {
        $fallbackResolver ??= $this->defaultFallbackResolver();
        $service = new RequestIdService($fallbackResolver);
        $service->setResolver(new HttpFoundationRequestResolver($request, $service));

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
