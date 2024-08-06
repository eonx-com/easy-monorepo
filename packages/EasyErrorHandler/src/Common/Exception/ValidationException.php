<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

abstract class ValidationException extends BadRequestException implements WithErrorListExceptionInterface
{
    use WithErrorListExceptionTrait;

    protected string $userMessage = 'exceptions.not_valid';
}
