<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Stub\Provider;

use Closure;
use EonX\EasyPipeline\Provider\MiddlewareProviderInterface;
use EonX\EasyPipeline\Provider\PipelineNameAwareInterface;
use EonX\EasyPipeline\Provider\PipelineNameAwareTrait;

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
