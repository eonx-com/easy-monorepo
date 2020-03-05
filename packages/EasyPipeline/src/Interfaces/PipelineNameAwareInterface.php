<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Interfaces;

interface PipelineNameAwareInterface
{
    public function setPipelineName(string $pipeline): void;
}
