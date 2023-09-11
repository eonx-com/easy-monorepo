<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Traits;

trait PipelineNameAwareTrait
{
    private string $pipelineName;

    public function setPipelineName(string $pipelineName): void
    {
        $this->pipelineName = $pipelineName;
    }
}
