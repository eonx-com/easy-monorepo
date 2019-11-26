<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPipeline\Exceptions;

use InvalidArgumentException;
use LoyaltyCorp\EasyPipeline\Interfaces\EasyPipelineExceptionInterface as ExceptionInterface;

final class EmptyPipelinesListException extends InvalidArgumentException implements ExceptionInterface
{
    // No body needed.
}


