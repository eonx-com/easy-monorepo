<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Helpers;

use Closure;
use Monolog\Processor\ProcessorInterface;

final class ProcessorWrapper implements ProcessorInterface
{
    private Closure $wrapped;

    public function __construct(callable $wrapped)
    {
        $this->wrapped = Closure::fromCallable($wrapped);
    }

    /**
     * @param mixed[] $records
     *
     * @return mixed[]
     */
    public function __invoke(array $records): array
    {
        $wrapped = $this->wrapped;

        return $wrapped($records);
    }

    public static function wrap(callable $wrapped): self
    {
        return new self($wrapped);
    }
}
