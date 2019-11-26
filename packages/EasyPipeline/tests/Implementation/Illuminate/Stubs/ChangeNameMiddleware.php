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

    /**
     * ChangeNameMiddleware constructor.
     *
     * @param string $changeTo
     */
    public function __construct(string $changeTo)
    {
        $this->changeTo = $changeTo;
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
            $input->setName($this->changeTo);
        }

        return $next($input);
    }
}


