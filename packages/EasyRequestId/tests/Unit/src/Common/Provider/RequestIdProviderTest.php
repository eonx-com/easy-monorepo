<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Tests\Unit\Common\Provider;

use EonX\EasyRandom\Generator\RandomGenerator;
use EonX\EasyRandom\Generator\RandomIntegerGenerator;
use EonX\EasyRandom\Generator\RandomStringGenerator;
use EonX\EasyRandom\Generator\UuidGenerator;
use EonX\EasyRequestId\Common\Provider\RequestIdProvider;
use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestId\Common\Resolver\FallbackResolverInterface;
use EonX\EasyRequestId\Common\Resolver\HttpFoundationRequestResolver;
use EonX\EasyRequestId\Common\Resolver\UuidFallbackResolver;
use EonX\EasyRequestId\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Factory\UuidFactory;
use Symfony\Component\Uid\Uuid;

final class RequestIdProviderTest extends AbstractUnitTestCase
{
    /**
     * @see testGetIds
     */
    public static function provideGetIdsData(): iterable
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
                RequestIdProviderInterface::DEFAULT_HTTP_HEADER_CORRELATION_ID => 'correlation-id',
                RequestIdProviderInterface::DEFAULT_HTTP_HEADER_REQUEST_ID => 'request-id',
            ]),
            'request-id',
            'correlation-id',
        ];
    }

    #[DataProvider('provideGetIdsData')]
    public function testGetIds(
        Request $request,
        ?string $requestId = null,
        ?string $correlationId = null,
        ?callable $assert = null,
        ?FallbackResolverInterface $fallbackResolver = null,
    ): void {
        $fallbackResolver ??= $this->defaultFallbackResolver();
        $requestIdProvider = new RequestIdProvider($fallbackResolver);
        $requestIdProvider->setResolver(new HttpFoundationRequestResolver($request, $requestIdProvider));

        // For caching coverage
        $requestIdProvider->getCorrelationId();
        $requestIdProvider->getRequestId();

        if ($requestId !== null) {
            self::assertSame($requestId, $requestIdProvider->getRequestId());
        }

        if ($correlationId !== null) {
            self::assertSame($correlationId, $requestIdProvider->getCorrelationId());
        }

        if ($assert !== null) {
            $assert($requestIdProvider->getRequestId(), $requestIdProvider->getCorrelationId());
        }
    }

    private function defaultFallbackResolver(): FallbackResolverInterface
    {
        return new UuidFallbackResolver(
            new RandomGenerator(
                new UuidGenerator(new UuidFactory()),
                new RandomIntegerGenerator(),
                new RandomStringGenerator()
            )
        );
    }
}
