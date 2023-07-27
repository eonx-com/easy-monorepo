<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Exceptions;

use EonX\EasyPipeline\Interfaces\EasyPipelineExceptionInterface;
use InvalidArgumentException;

final class EmptyMiddlewareListException extends InvalidArgumentException implements EasyPipelineExceptionInterface
{
    // No body needed
}
