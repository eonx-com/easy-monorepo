<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ErrorResponseFactoryInterface
{
    public function create(Request $request, ErrorResponseDataInterface $data): Response;
}
