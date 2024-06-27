<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Factory;

use EonX\EasyPipeline\Pipeline\PipelineInterface;

interface PipelineFactoryInterface
{
    public function create(string $pipeline): PipelineInterface;
}
