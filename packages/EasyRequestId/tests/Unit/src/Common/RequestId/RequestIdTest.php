<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Unit\Common\RequestId;

use EonX\EasyRandom\Bridge\Symfony\Generators\SymfonyUuidV6Generator;
use EonX\EasyRandom\Generators\RandomGenerator;
use EonX\EasyRequestId\Common\RequestId\RequestId;
use EonX\EasyRequestId\Common\RequestId\RequestIdInterface;
use EonX\EasyRequestId\Common\Resolver\FallbackResolverInterface;
use EonX\EasyRequestId\Common\Resolver\HttpFoundationRequestResolver;
use EonX\EasyRequestId\Common\Resolver\UuidFallbackResolver;
use EonX\EasyRequestId\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

final class RequestIdTest extends AbstractUnitTestCase
{
    /**
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
                RequestIdInterface::DEFAULT_HTTP_HEADER_CORRELATION_ID => 'correlation-id',
                RequestIdInterface::DEFAULT_HTTP_HEADER_REQUEST_ID => 'request-id',
            ]),
            'request-id',
            'correlation-id',
        ];
    }

    #[DataProvider('providerTestGetIds')]
    public function testGetIds(
        Request $request,
        ?string $requestId = null,
        ?string $correlationId = null,
        ?callable $assert = null,
        ?FallbackResolverInterface $fallbackResolver = null,
    ): void {
        $fallbackResolver ??= $this->defaultFallbackResolver();
        $service = new RequestId($fallbackResolver);
        $service->setResolver(new HttpFoundationRequestResolver($request, $service));

        // For caching coverage
        $service->getCorrelationId();
        $service->getRequestId();

        if ($requestId !== null) {
            self::assertSame($requestId, $service->getRequestId());
        }

        if ($correlationId !== null) {
            self::assertSame($correlationId, $service->getCorrelationId());
        }

        if ($assert !== null) {
            $assert($service->getRequestId(), $service->getCorrelationId());
        }
    }

    private function defaultFallbackResolver(): FallbackResolverInterface
    {
        return new UuidFallbackResolver(
            new RandomGenerator(new SymfonyUuidV6Generator())
        );
    }
}
