<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Entity;

use BackedEnum;
use InvalidArgumentException;

final readonly class Actor implements ActorInterface
{
    private string $type;

    public function __construct(
        string|BackedEnum $type,
        private ?string $id = null,
        private ?string $name = null,
    ) {
        $typeValue = \is_string($type) ? $type : $type->value;

        if (\is_string($typeValue) === false) {
            throw new InvalidArgumentException(
                \sprintf('The backed case of the "%s" backed enum has to be a string.', $type::class)
            );
        }

        $this->type = $typeValue;
    }

    public function getActorId(): ?string
    {
        return $this->id;
    }

    public function getActorName(): ?string
    {
        return $this->name;
    }

    public function getActorType(): string
    {
        return $this->type;
    }
}
