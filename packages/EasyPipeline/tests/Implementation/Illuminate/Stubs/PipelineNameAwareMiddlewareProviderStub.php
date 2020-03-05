<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Implementation\Illuminate\Stubs;

use EonX\EasyPipeline\Interfaces\MiddlewareProviderInterface;
use EonX\EasyPipeline\Interfaces\PipelineNameAwareInterface;
use EonX\EasyPipeline\Traits\PipelineNameAwareTrait;

final class PipelineNameAwareMiddlewareProviderStub implements MiddlewareProviderInterface, PipelineNameAwareInterface
{
    use PipelineNameAwareTrait;

    /**
     * @param mixed $input
     */
    public function actAsMiddleware($input, \Closure $next): string
    {
        $input .= $this->pipelineName;

        return $next($input);
    }

    /**
     * @return mixed[]
     */
    public function getMiddlewareList(): array
    {
        return [
            [$this, 'actAsMiddleware']
        ];
    }
}
