<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Implementation\Illuminate\Stubs;

use EonX\EasyPipeline\Interfaces\MiddlewareInterface;

final class ChangeNameMiddleware implements MiddlewareInterface
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
