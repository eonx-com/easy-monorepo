<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPsr7Factory\Tests;

use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\EasyPsr7Factory\EasyPsr7Factory;
use Symfony\Bridge\PsrHttpMessage\Tests\Fixtures\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class EasyPsr7FactoryTest extends AbstractTestCase
{
    /**
     * Factory should create the right request with the values of the original.
     *
     * @return void
     */
    public function testCreateRequest(): void
    {
        $psr7Factory = $this->getFactory();

        $uri = 'google.com';
        $value = 'value-query-1';

        $symfonyRequest = new Request(['query1' => $value], [], [], [], [], ['HTTP_HOST' => $uri]);
        $psrRequest = $psr7Factory->createRequest($symfonyRequest);

        self::assertInstanceOf(ServerRequestInterface::class, $psrRequest);
        self::assertEquals($value, $psrRequest->getQueryParams()['query1']);
        self::assertEquals('/', $psrRequest->getRequestTarget());
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
     * @return \StepTheFkUp\EasyPsr7Factory\EasyPsr7Factory
     */
    private function getFactory(): EasyPsr7Factory
    {
        return new EasyPsr7Factory();
    }
}

\class_alias(
    EasyPsr7FactoryTest::class,
    'LoyaltyCorp\EasyPsr7Factory\Tests\EasyPsr7FactoryTest',
    false
);
