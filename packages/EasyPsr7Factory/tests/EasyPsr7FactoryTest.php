<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Tests;

use EonX\EasyPsr7Factory\EasyPsr7Factory;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class EasyPsr7FactoryTest extends AbstractTestCase
{
    public function testCreateRequest(): void
    {
        $psr7Factory = $this->getFactory();

        $uri = 'eonx.com';
        $value = 'value-query-1';

        $symfonyRequest = new Request([
            'query1' => $value,
        ], [], [], [], [], [
            'HTTP_HOST' => $uri,
        ]);
        $psrRequest = $psr7Factory->createRequest($symfonyRequest);

        self::assertInstanceOf(ServerRequestInterface::class, $psrRequest);
        self::assertEquals($value, $psrRequest->getQueryParams()['query1']);
        self::assertEquals('/', $psrRequest->getRequestTarget());
    }

    public function testCreateResponse(): void
    {
        $psr7Factory = $this->getFactory();

        $psrResponse = new Response();
        $symfonyResponse = $psr7Factory->createResponse($psrResponse);

        self::assertInstanceOf(SymfonyResponse::class, $symfonyResponse);
    }

    private function getFactory(): EasyPsr7Factory
    {
        return new EasyPsr7Factory();
    }
}
