<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Interfaces;

interface EasyPipelineExceptionInterface
{
    // Marker for all exceptions of this package.
}

\class_alias(
    EasyPipelineExceptionInterface::class,
    'LoyaltyCorp\EasyPipeline\Interfaces\EasyPipelineExceptionInterface',
    false
);
