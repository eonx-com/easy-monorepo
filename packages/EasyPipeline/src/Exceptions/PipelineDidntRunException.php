<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Exceptions;

use EonX\EasyPipeline\Interfaces\EasyPipelineExceptionInterface;
use LogicException;

final class PipelineDidntRunException extends LogicException implements EasyPipelineExceptionInterface
{
    // No body needed
}
