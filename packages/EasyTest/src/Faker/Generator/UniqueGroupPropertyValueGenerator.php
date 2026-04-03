<?php
declare(strict_types=1);

namespace EonX\EasyTest\Faker\Generator;

use Faker\Generator;
use Stringable;
use UnexpectedValueException;

final class UniqueGroupPropertyValueGenerator
{
    private array $callbackStack = [];

    public function __construct(
        private readonly Generator $generator,
        private readonly array $arguments,
        private readonly string $name,
        private readonly string $uniqueGroupName,
    ) {}

    public function __call(string $name, array $args): self
    {
        $this->callbackStack[] = [
            'arguments' => $args,
            'method' => $name,
        ];

        return $this;
    }

    public function generateValue(): string
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

            if (
                $callbackResult !== null
                && \is_scalar($callbackResult) === false
                && $callbackResult instanceof Stringable === false
            ) {
                throw new UnexpectedValueException(\sprintf(
                    'The value generated for unique group "%s" is not a string or stringable. Got: %s',
                    $this->uniqueGroupName,
                    \get_debug_type($callbackResult)
                ));
            }

            return (string)$callbackResult;
        }

        return '';
    }

    public function getGroupName(): string
    {
        return $this->uniqueGroupName;
    }
}
