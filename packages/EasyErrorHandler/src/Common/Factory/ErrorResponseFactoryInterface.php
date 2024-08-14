<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Factory;

use EonX\EasyErrorHandler\Common\ValueObject\ErrorResponseData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ErrorResponseFactoryInterface
{
    public function create(Request $request, ErrorResponseData $data): Response;
}
