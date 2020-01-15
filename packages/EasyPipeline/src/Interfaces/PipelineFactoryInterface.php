<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Interfaces;

interface PipelineFactoryInterface
{
    /**
     * Create pipeline for given name and input.
     *
     * @param string $pipeline The pipeline name
     *
     * @return \EonX\EasyPipeline\Interfaces\PipelineInterface
     *
     * @throws \EonX\EasyPipeline\Exceptions\PipelineNotFoundException If given pipeline not found
     */
    public function create(string $pipeline): PipelineInterface;
}
