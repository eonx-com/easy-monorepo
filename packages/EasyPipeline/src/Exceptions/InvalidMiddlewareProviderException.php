<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Exceptions;

use InvalidArgumentException;
use StepTheFkUp\EasyPipeline\Interfaces\EasyPipelineExceptionInterface as ExceptionInterface;

final class InvalidMiddlewareProviderException extends InvalidArgumentException implements ExceptionInterface
{
    // No body needed.
}

\class_alias(
    InvalidMiddlewareProviderException::class,
    'LoyaltyCorp\EasyPipeline\Exceptions\InvalidMiddlewareProviderException',
    false
);
