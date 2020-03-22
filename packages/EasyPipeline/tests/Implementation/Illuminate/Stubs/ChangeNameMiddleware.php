<?php

declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Implementation\Illuminate\Stubs;

use EonX\EasyPipeline\Interfaces\MiddlewareInterface;

final class ChangeNameMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $changeTo;

    public function __construct(string $changeTo)
    {
        $this->changeTo = $changeTo;
    }

    /**
     * @param mixed $input
     *
     * @return mixed
     */
    public function handle($input, callable $next)
    {
        if ($input instanceof InputStub) {
            $input->setName($this->changeTo);
        }

        return $next($input);
    }
}
