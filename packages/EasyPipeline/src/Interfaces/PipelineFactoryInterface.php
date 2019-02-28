<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Interfaces;

interface PipelineFactoryInterface
{
    /**
     * Create pipeline for given name and input.
     *
     * @param string $pipeline The pipeline name
     * @param mixed $input The input to process
     *
     * @return \StepTheFkUp\EasyPipeline\Interfaces\PipelineInterface
     *
     * @throws \StepTheFkUp\EasyPipeline\Exceptions\PipelineNotFoundException If given pipeline not found
     */
    public function create(string $pipeline, $input): PipelineInterface;
}
