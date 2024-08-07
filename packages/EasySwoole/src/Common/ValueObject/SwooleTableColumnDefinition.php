<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\ValueObject;

use BackedEnum;
use EonX\EasySwoole\Common\Enum\SwooleTableColumnType;
use InvalidArgumentException;

final readonly class SwooleTableColumnDefinition
{
    public string $name;

    public function __construct(
        string|BackedEnum $name,
        public SwooleTableColumnType $type,
        public ?int $size = null,
    ) {
        $nameValue = \is_string($name) ? $name : $name->value;

        if (\is_string($nameValue) === false) {
            throw new InvalidArgumentException(
                \sprintf('The backed case of the "%s" backed enum has to be a string.', $name::class)
            );
        }

        $this->name = $nameValue;
    }
}
