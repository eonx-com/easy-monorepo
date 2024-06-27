<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Exception;

use InvalidArgumentException;

final class PipelineNotFoundException extends InvalidArgumentException implements EasyPipelineExceptionInterface
{
    // No body needed
}
