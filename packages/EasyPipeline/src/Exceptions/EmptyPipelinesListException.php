<?php

declare(strict_types=1);

namespace EonX\EasyPipeline\Exceptions;

use EonX\EasyPipeline\Interfaces\EasyPipelineExceptionInterface as ExceptionInterface;
use InvalidArgumentException;

final class EmptyPipelinesListException extends InvalidArgumentException implements ExceptionInterface
{
    // No body needed
}
