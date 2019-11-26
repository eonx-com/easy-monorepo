<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Exceptions;

use InvalidArgumentException;
use EonX\EasyPipeline\Interfaces\EasyPipelineExceptionInterface;

final class PipelineNotFoundException extends InvalidArgumentException implements EasyPipelineExceptionInterface
{
    // No body needed.
}


