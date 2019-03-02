<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Tests\Implementation\Illuminate\Stubs;

use StepTheFkUp\EasyPipeline\Interfaces\MiddlewareInterface;
use StepTheFkUp\EasyPipeline\Interfaces\MiddlewareLoggerAwareInterface;
use StepTheFkUp\EasyPipeline\Traits\MiddlewareLoggerAwareTrait;

final class LoggerChangeNameMiddleware implements MiddlewareInterface, MiddlewareLoggerAwareInterface
{
    use MiddlewareLoggerAwareTrait;

    /**
     * @var \StepTheFkUp\EasyPipeline\Tests\Implementation\Illuminate\Stubs\ChangeNameMiddleware
     */
    private $decorated;

    /**
     * LoggerChangeNameMiddleware constructor.
     *
     * @param \StepTheFkUp\EasyPipeline\Tests\Implementation\Illuminate\Stubs\ChangeNameMiddleware $decorated
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
