<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Exceptions;

use InvalidArgumentException;
use StepTheFkUp\EasyPipeline\Interfaces\EasyPipelineExceptionInterface;

final class PipelineNotFoundException extends InvalidArgumentException implements EasyPipelineExceptionInterface
{
    // No body needed.
}

\class_alias(
    PipelineNotFoundException::class,
    'LoyaltyCorp\EasyPipeline\Exceptions\PipelineNotFoundException',
    false
);
