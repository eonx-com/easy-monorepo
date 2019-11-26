<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPipeline\Exceptions;

use InvalidArgumentException;
use LoyaltyCorp\EasyPipeline\Interfaces\EasyPipelineExceptionInterface;

final class PipelineNotFoundException extends InvalidArgumentException implements EasyPipelineExceptionInterface
{
    // No body needed.
}


