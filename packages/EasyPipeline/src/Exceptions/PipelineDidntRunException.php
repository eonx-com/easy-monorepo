<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPipeline\Exceptions;

use LogicException;
use LoyaltyCorp\EasyPipeline\Interfaces\EasyPipelineExceptionInterface;

final class PipelineDidntRunException extends LogicException implements EasyPipelineExceptionInterface
{
    // No body needed.
}

\class_alias(
    PipelineDidntRunException::class,
    'StepTheFkUp\EasyPipeline\Exceptions\PipelineDidntRunException',
    false
);
