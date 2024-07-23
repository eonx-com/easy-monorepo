<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Processor;

use Closure;
use Monolog\Processor\ProcessorInterface;

final class WrappingProcessor implements ProcessorInterface
{
    private Closure $wrapped;

    public function __construct(callable $wrapped)
    {
        $this->wrapped = $wrapped(...);
    }

    public function __invoke(array $record): array
    {
        $wrapped = $this->wrapped;

        return $wrapped($record);
    }

    public static function wrap(callable $wrapped): self
    {
        return new self($wrapped);
    }
}
