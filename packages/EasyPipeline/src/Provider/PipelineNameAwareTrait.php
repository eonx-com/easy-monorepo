<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Provider;

trait PipelineNameAwareTrait
{
    private string $pipelineName;

    public function setPipelineName(string $pipelineName): void
    {
        $this->pipelineName = $pipelineName;
    }
}
