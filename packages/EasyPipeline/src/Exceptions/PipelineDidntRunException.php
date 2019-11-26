<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Exceptions;

use LogicException;
use EonX\EasyPipeline\Interfaces\EasyPipelineExceptionInterface;

final class PipelineDidntRunException extends LogicException implements EasyPipelineExceptionInterface
{
    // No body needed.
}


