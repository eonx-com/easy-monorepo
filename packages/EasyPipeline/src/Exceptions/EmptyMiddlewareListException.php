<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPipeline\Exceptions;

use LoyaltyCorp\EasyPipeline\Interfaces\EasyPipelineExceptionInterface;

final class EmptyMiddlewareListException extends \InvalidArgumentException implements EasyPipelineExceptionInterface
{
    // No body needed.
}

\class_alias(
    EmptyMiddlewareListException::class,
    'StepTheFkUp\EasyPipeline\Exceptions\EmptyMiddlewareListException',
    false
);
