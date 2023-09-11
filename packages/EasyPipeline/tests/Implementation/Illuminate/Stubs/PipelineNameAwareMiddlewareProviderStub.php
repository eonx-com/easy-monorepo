<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Implementation\Illuminate\Stubs;

use Closure;
use EonX\EasyPipeline\Interfaces\MiddlewareProviderInterface;
use EonX\EasyPipeline\Interfaces\PipelineNameAwareInterface;
use EonX\EasyPipeline\Traits\PipelineNameAwareTrait;

final class PipelineNameAwareMiddlewareProviderStub implements MiddlewareProviderInterface, PipelineNameAwareInterface
{
    use PipelineNameAwareTrait;

    public function actAsMiddleware(mixed $input, Closure $next): string
    {
        $input .= $this->pipelineName;

        return $next($input);
    }

    public function getMiddlewareList(): array
    {
        return [$this->actAsMiddleware(...)];
    }
}
