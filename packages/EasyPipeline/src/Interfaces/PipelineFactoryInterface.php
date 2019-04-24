<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPipeline\Interfaces;

interface PipelineFactoryInterface
{
    /**
     * Create pipeline for given name and input.
     *
     * @param string $pipeline The pipeline name
     *
     * @return \LoyaltyCorp\EasyPipeline\Interfaces\PipelineInterface
     *
     * @throws \LoyaltyCorp\EasyPipeline\Exceptions\PipelineNotFoundException If given pipeline not found
     */
    public function create(string $pipeline): PipelineInterface;
}

\class_alias(
    PipelineFactoryInterface::class,
    'StepTheFkUp\EasyPipeline\Interfaces\PipelineFactoryInterface',
    false
);
