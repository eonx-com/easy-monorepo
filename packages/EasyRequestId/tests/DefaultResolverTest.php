<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Tests;

use EonX\EasyRequestId\DefaultResolver;
use EonX\EasyRequestId\Interfaces\ResolverInterface;
use Symfony\Component\HttpFoundation\Request;

final class DefaultResolverTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestResolve(): iterable
    {
        yield 'Default empty request' => [
            new Request(),
        ];

        yield 'Default headers' => [
            $this->getRequestWithHeaders([
                ResolverInterface::DEFAULT_CORRELATION_ID_HEADER => 'correlation',
                ResolverInterface::DEFAULT_REQUEST_ID_HEADER => 'request',
            ]),
            'request',
            'correlation',
        ];

        yield 'Custom headers' => [
            $this->getRequestWithHeaders([
                'x-custom-correlation' => 'correlation',
                'x-custom-request' => 'request',
            ]),
            'request',
            'correlation',
            'x-custom-request',
            'x-custom-correlation',
        ];
    }

    /**
     * @dataProvider providerTestResolve
     */
    public function testResolve(
        Request $request,
        ?string $requestId = null,
        ?string $correlationId = null,
        ?string $requestIdHeader = null,
        ?string $correlationIdHeader = null
    ): void {
        $resolver = new DefaultResolver($requestIdHeader, $correlationIdHeader);

        self::assertEquals($requestId, $resolver->getRequestId($request));
        self::assertEquals($correlationId, $resolver->getCorrelationId($request));
        self::assertEquals(0, $resolver->getPriority());
    }
}
