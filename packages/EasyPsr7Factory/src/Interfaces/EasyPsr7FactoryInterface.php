<?php

declare(strict_types=1);

namespace EonX\EasyPsr7Factory\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface EasyPsr7FactoryInterface
{
    public function createRequest(Request $request): ServerRequestInterface;

    public function createResponse(ResponseInterface $response): Response;
}
