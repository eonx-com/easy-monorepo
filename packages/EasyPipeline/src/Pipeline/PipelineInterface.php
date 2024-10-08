<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Pipeline;

interface PipelineInterface
{
    public function getLogs(): array;

    public function process(mixed $input): mixed;
}
