<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Exception;

use InvalidArgumentException;

final class EmptyMiddlewareListException extends InvalidArgumentException implements EasyPipelineExceptionInterface
{
    // No body needed
}
