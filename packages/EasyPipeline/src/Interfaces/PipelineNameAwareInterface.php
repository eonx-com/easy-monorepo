<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPipeline\Interfaces;

interface PipelineNameAwareInterface
{
    /**
     * Set the current pipeline name on a MiddlewareProvider.
     *
     * @param string $pipeline The pipeline name
     *
     * @return void
     */
    public function setPipelineName(string $pipeline): void;
}

\class_alias(
    PipelineNameAwareInterface::class,
    'StepTheFkUp\EasyPipeline\Interfaces\PipelineNameAwareInterface',
    false
);
