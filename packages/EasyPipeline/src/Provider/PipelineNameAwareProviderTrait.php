<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Provider;

trait PipelineNameAwareProviderTrait
{
    private string $pipelineName;

    public function setPipelineName(string $pipelineName): void
    {
        $this->pipelineName = $pipelineName;
    }
}
