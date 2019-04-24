<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPipeline\Exceptions;

use InvalidArgumentException;
use LoyaltyCorp\EasyPipeline\Interfaces\EasyPipelineExceptionInterface as ExceptionInterface;

final class InvalidMiddlewareProviderException extends InvalidArgumentException implements ExceptionInterface
{
    // No body needed.
}

\class_alias(
    InvalidMiddlewareProviderException::class,
    'StepTheFkUp\EasyPipeline\Exceptions\InvalidMiddlewareProviderException',
    false
);
