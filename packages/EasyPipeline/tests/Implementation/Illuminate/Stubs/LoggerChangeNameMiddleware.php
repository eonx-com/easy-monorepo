<?php

declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Implementation\Illuminate\Stubs;

use EonX\EasyPipeline\Interfaces\MiddlewareInterface;
use EonX\EasyPipeline\Interfaces\MiddlewareLoggerAwareInterface;
use EonX\EasyPipeline\Traits\MiddlewareLoggerAwareTrait;

final class LoggerChangeNameMiddleware implements MiddlewareInterface, MiddlewareLoggerAwareInterface
{
    use MiddlewareLoggerAwareTrait;

    /**
     * @var \EonX\EasyPipeline\Tests\Implementation\Illuminate\Stubs\ChangeNameMiddleware
     */
    private $decorated;

    public function __construct(ChangeNameMiddleware $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @param mixed $input
     *
     * @return mixed
     */
    public function handle($input, callable $next)
    {
        if ($input instanceof InputStub) {
            $previousName = $input->getName();

            $this->decorated->handle($input, function (InputStub $input) use ($previousName) {
                $this->log(\sprintf('Changed name "%s" to "%s"', $previousName, $input->getName()));

                return $input;
            });
        }

        return $next($input);
    }
}
