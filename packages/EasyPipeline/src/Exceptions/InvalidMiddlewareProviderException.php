<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Exceptions;

use InvalidArgumentException;
use StepTheFkUp\EasyPipeline\Interfaces\EasyPipelineExceptionInterface as ExceptionInterface;

final class InvalidMiddlewareProviderException extends InvalidArgumentException implements ExceptionInterface
{
    // No body needed.
}
