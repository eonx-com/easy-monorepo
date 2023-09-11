<?php
declare(strict_types=1);

namespace EonX\EasyActivity;

use EonX\EasyActivity\Interfaces\ActorInterface;

final class Actor implements ActorInterface
{
    public function __construct(
        private string $type,
        private ?string $id = null,
        private ?string $name = null,
    ) {
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
