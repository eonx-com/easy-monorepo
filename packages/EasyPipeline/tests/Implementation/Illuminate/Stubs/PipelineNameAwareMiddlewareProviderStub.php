<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Tests\Implementation\Illuminate\Stubs;

use StepTheFkUp\EasyPipeline\Interfaces\MiddlewareProviderInterface;
use StepTheFkUp\EasyPipeline\Interfaces\PipelineNameAwareInterface;
use StepTheFkUp\EasyPipeline\Traits\PipelineNameAwareTrait;

final class PipelineNameAwareMiddlewareProviderStub implements MiddlewareProviderInterface, PipelineNameAwareInterface
{
    use PipelineNameAwareTrait;

    /**
     * A simple middleware to return the current pipeline name.
     *
     * @param mixed $input
     * @param \Closure $next
     *
     * @return string
     */
    public function actAsMiddleware($input, \Closure $next): string
    {
        $input .= $this->pipelineName;

        return $next($input);
    }

    /**
     * Return just the middleware defined above.
     *
     * @return mixed[]
     */
    public function getMiddlewareList(): array
    {
        return [
            [$this, 'actAsMiddleware']
        ];
    }
}
