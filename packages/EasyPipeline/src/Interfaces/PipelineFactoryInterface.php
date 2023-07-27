<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Interfaces;

interface PipelineFactoryInterface
{
    public function create(string $pipeline): PipelineInterface;
}
