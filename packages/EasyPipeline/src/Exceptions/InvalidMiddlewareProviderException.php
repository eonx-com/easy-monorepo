<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Exceptions;

use InvalidArgumentException;
use EonX\EasyPipeline\Interfaces\EasyPipelineExceptionInterface as ExceptionInterface;

final class InvalidMiddlewareProviderException extends InvalidArgumentException implements ExceptionInterface
{
    // No body needed.
}


