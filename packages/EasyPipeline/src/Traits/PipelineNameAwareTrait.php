<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Traits;

trait PipelineNameAwareTrait
{
    /**
     * @var string
     */
    private $pipelineName;

    public function setPipelineName(string $pipelineName): void
    {
        $this->pipelineName = $pipelineName;
    }
}
