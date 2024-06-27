<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Exception;

use LogicException;

final class PipelineDidNotRunException extends LogicException implements EasyPipelineExceptionInterface
{
    // No body needed
}
