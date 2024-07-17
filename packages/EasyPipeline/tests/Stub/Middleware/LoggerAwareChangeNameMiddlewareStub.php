<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Stub\Middleware;

use EonX\EasyPipeline\Logger\MiddlewareLoggerAwareInterface;
use EonX\EasyPipeline\Logger\MiddlewareLoggerAwareTrait;
use EonX\EasyPipeline\Middleware\MiddlewareInterface;
use EonX\EasyPipeline\Tests\Stub\Input\InputStub;

final class LoggerAwareChangeNameMiddlewareStub implements MiddlewareInterface, MiddlewareLoggerAwareInterface
{
    use MiddlewareLoggerAwareTrait;

    public function __construct(
        private ChangeNameMiddlewareStub $decorated,
    ) {
    }

    public function handle(mixed $input, callable $next): mixed
    {
        if ($input instanceof InputStub) {
            $previousName = $input->getName();

            $this->decorated->handle($input, function (InputStub $input) use ($previousName): InputStub {
                $this->log(\sprintf('Changed name "%s" to "%s"', $previousName, $input->getName()));

                return $input;
            });
        }

        return $next($input);
    }
}
