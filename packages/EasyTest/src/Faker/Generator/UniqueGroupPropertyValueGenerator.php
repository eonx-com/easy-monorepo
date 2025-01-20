<?php
declare(strict_types=1);

namespace EonX\EasyTest\Faker\Generator;

use Faker\Generator;

final class UniqueGroupPropertyValueGenerator
{
    private array $callbackStack = [];

    public function __construct(
        private readonly Generator $generator,
        private readonly array $arguments,
        private readonly string $name,
        private readonly string $uniqueGroupName,
    ) {
    }

    public function __call(string $name, array $args): self
    {
        $this->callbackStack[] = [
            'arguments' => $args,
            'method' => $name,
        ];

        return $this;
    }

    public function generateValue(): mixed
    {
        $callback = [$this->generator, $this->name];

        if (\is_callable($callback)) {
            $callbackResult = \call_user_func_array($callback, $this->arguments);

            if (\count($this->callbackStack) > 0) {
                foreach ($this->callbackStack as $callbackData) {
                    /** @var callable $callback */
                    $callback = [$callbackResult, $callbackData['method']];
                    $callbackResult = \call_user_func_array($callback, $callbackData['arguments']);
                }
            }

            return $callbackResult;
        }

        return null;
    }

    public function getGroupName(): string
    {
        return $this->uniqueGroupName;
    }
}
