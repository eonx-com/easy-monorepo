<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Exceptions;

use LogicException;
use StepTheFkUp\EasyPipeline\Interfaces\EasyPipelineExceptionInterface;

final class PipelineDidntRunException extends LogicException implements EasyPipelineExceptionInterface
{
    // No body needed.
}

\class_alias(
    PipelineDidntRunException::class,
    'LoyaltyCorp\EasyPipeline\Exceptions\PipelineDidntRunException',
    false
);
