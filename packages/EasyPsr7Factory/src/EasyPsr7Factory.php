<?php
declare(strict_types=1);

namespace EonX\EasyPsr7Factory;

use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\UploadedFileFactory;

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
        $this->httpMessage = $httpMessage ?? new PsrHttpFactory(
            new ServerRequestFactory(),
            new StreamFactory(),
            new UploadedFileFactory(),
            new ResponseFactory()
        );
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
