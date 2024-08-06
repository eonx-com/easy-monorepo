<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Provider;

interface PipelineNameAwareProviderInterface
{
    public function setPipelineName(string $pipeline): void;
}
