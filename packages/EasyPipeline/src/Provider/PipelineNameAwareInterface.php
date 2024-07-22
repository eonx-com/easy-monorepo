<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Provider;

interface PipelineNameAwareInterface
{
    public function setPipelineName(string $pipeline): void;
}
