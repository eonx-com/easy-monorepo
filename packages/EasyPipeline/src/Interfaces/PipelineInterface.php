<?php

declare(strict_types=1);

namespace EonX\EasyPipeline\Interfaces;

interface PipelineInterface
{
    /**
     * @param mixed $input The input to be processed
     *
     * @return mixed
     */
    public function process($input);

    /**
     * @return mixed[]
     */
    public function getLogs(): array;
}
