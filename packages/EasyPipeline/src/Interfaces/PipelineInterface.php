<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPipeline\Interfaces;

interface PipelineInterface
{
    /**
     * Process set input through set middleware list and return processed input.
     *
     * @param mixed $input The input to be processed
     *
     * @return mixed
     */
    public function process($input);

    /**
     * Return logs created by each middleware during process.
     *
     * @return mixed[]
     *
     * @throws \LoyaltyCorp\EasyPipeline\Exceptions\PipelineDidntRunException If called before process() is called
     */
    public function getLogs(): array;
}


