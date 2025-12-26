<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Stub\Provider;

use Closure;
use EonX\EasyPipeline\Provider\MiddlewareProviderInterface;
use EonX\EasyPipeline\Provider\PipelineNameAwareProviderInterface;
use EonX\EasyPipeline\Provider\PipelineNameAwareProviderTrait;

final class PipelineNameAwareProviderMiddlewareProviderStub implements
    MiddlewareProviderInterface,
    PipelineNameAwareProviderInterface
{
    use PipelineNameAwareProviderTrait;

    public function actAsMiddleware(string $input, Closure $next): string
    {
        $input .= $this->pipelineName;

        return $next($input);
    }

    public function getMiddlewareList(): array
    {
        return [$this->actAsMiddleware(...)];
    }
}
