<?php
declare(strict_types=1);

namespace StepTheFkUp\Psr7Factory\Tests;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\Psr7Factory\Psr7Factory;
use Symfony\Bridge\PsrHttpMessage\Tests\Fixtures\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Psr7FactoryTest extends AbstractTestCase
{
    /**
     * Factory should create the right request with the values of the original.
     *
     * @return void
     */
    public function testCreateRequest(): void
    {
        $psr7Factory = $this->getFactory();

        $uri = 'https://google.com';
        $value = 'value-query-1';

        $symfonyRequest = new Request(['query1' => $value], [], [], [], [], ['ORIG_PATH_INFO' => $uri]);
        $psrRequest = $psr7Factory->createRequest($symfonyRequest);

        self::assertInstanceOf(ServerRequestInterface::class, $psrRequest);
        self::assertEquals($value, $psrRequest->getQueryParams()['query1']);
        self::assertEquals($uri, $psrRequest->getRequestTarget());
    }

    /**
     * Factory should create the right response from the original.
     *
     * @return void
     */
    public function testCreateResponse(): void
    {
        $psr7Factory = $this->getFactory();

        $psrResponse = new Response();
        $symfonyResponse = $psr7Factory->createResponse($psrResponse);

        self::assertInstanceOf(SymfonyResponse::class, $symfonyResponse);
    }

    /**
     * Get PSR-7 factory.
     *
     * @return \StepTheFkUp\Psr7Factory\Psr7Factory
     */
    private function getFactory(): Psr7Factory
    {
        return new Psr7Factory();
    }
}
