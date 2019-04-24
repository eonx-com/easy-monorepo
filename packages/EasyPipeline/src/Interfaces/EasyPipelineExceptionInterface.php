<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPipeline\Interfaces;

interface EasyPipelineExceptionInterface
{
    // Marker for all exceptions of this package.
}

\class_alias(
    EasyPipelineExceptionInterface::class,
    'StepTheFkUp\EasyPipeline\Interfaces\EasyPipelineExceptionInterface',
    false
);
