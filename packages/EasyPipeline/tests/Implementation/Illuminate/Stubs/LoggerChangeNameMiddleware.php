<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPipeline\Tests\Implementation\Illuminate\Stubs;

use LoyaltyCorp\EasyPipeline\Interfaces\MiddlewareInterface;
use LoyaltyCorp\EasyPipeline\Interfaces\MiddlewareLoggerAwareInterface;
use LoyaltyCorp\EasyPipeline\Traits\MiddlewareLoggerAwareTrait;

final class LoggerChangeNameMiddleware implements MiddlewareInterface, MiddlewareLoggerAwareInterface
{
    use MiddlewareLoggerAwareTrait;

    /**
     * @var \LoyaltyCorp\EasyPipeline\Tests\Implementation\Illuminate\Stubs\ChangeNameMiddleware
     */
    private $decorated;

    /**
     * LoggerChangeNameMiddleware constructor.
     *
     * @param \LoyaltyCorp\EasyPipeline\Tests\Implementation\Illuminate\Stubs\ChangeNameMiddleware $decorated
     */
    public function __construct(ChangeNameMiddleware $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * Handle given input and pass return through next.
     *
     * @param mixed $input
     * @param callable $next
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


