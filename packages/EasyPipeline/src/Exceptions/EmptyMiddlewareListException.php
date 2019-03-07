<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Exceptions;

use StepTheFkUp\EasyPipeline\Interfaces\EasyPipelineExceptionInterface;

final class EmptyMiddlewareListException extends \InvalidArgumentException implements EasyPipelineExceptionInterface
{
    // No body needed.
}
