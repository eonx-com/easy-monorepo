<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPsr7Factory;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use StepTheFkUp\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class EasyPsr7Factory implements EasyPsr7FactoryInterface
{
    /**
     * @var \Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface
     */
    private $httpFoundation;

    /**
     * @var \Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface
     */
    private $httpMessage;

    /**
     * EasyEasyPsr7Factory constructor.
     *
     * @param \Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface|null $httpFoundation
     * @param \Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface|null $httpMessage
     */
    public function __construct(
        ?HttpFoundationFactoryInterface $httpFoundation = null,
        ?HttpMessageFactoryInterface $httpMessage = null
    ) {
        $this->httpFoundation = $httpFoundation ?? new HttpFoundationFactory();
        $this->httpMessage = $httpMessage ?? new DiactorosFactory();
    }

    /**
     * Create PSR-7 request from Symfony request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function createRequest(Request $request): ServerRequestInterface
    {
        return $this->httpMessage->createRequest($request);
    }

    /**
     * Create Symfony response from PSR-7 response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createResponse(ResponseInterface $response): Response
    {
        return $this->httpFoundation->createResponse($response);
    }
}

\class_alias(
    EasyPsr7Factory::class,
    'LoyaltyCorp\EasyPsr7Factory\EasyPsr7Factory',
    false
);
