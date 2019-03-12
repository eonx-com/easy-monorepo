<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Traits;

trait PipelineNameAwareTrait
{
    /**
     * @var string
     */
    private $pipelineName;

    /**
     * Set pipeline name.
     *
     * @param string $pipelineName
     *
     * @return void
     */
    public function setPipelineName(string $pipelineName): void
    {
        $this->pipelineName = $pipelineName;
    }
}
