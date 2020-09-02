<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Helpers;

use Monolog\Processor\ProcessorInterface;

final class ProcessorWrapper implements ProcessorInterface
{
    /**
     * @var callable
     */
    private $wrapped;

    public function __construct(callable $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    public static function wrap(callable $wrapped): self
    {
        return new self($wrapped);
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
}
