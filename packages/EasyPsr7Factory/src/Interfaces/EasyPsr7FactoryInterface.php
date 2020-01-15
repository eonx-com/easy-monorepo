<?php
declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface EasyPsr7FactoryInterface
{
    /**
     * Create PSR-7 request from Symfony request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function createRequest(Request $request): ServerRequestInterface;

    /**
     * Create Symfony response from PSR-7 response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createResponse(ResponseInterface $response): Response;
}
