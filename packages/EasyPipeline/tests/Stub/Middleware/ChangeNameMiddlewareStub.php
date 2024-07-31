<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Stub\Middleware;

use EonX\EasyPipeline\Middleware\MiddlewareInterface;
use EonX\EasyPipeline\Tests\Stub\Input\InputStub;

final readonly class ChangeNameMiddlewareStub implements MiddlewareInterface
{
    public function __construct(
        private string $changeTo,
    ) {
    }

    public function handle(mixed $input, callable $next): mixed
    {
        if ($input instanceof InputStub) {
            $input->setName($this->changeTo);
        }

        return $next($input);
    }
}
