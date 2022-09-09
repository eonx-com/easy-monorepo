<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

use Symfony\Component\HttpFoundation\Response;

abstract class NotFoundException extends BaseException
{
    protected int $statusCode = Response::HTTP_NOT_FOUND;

    protected ?string $userMessage = 'exceptions.not_found';
}
