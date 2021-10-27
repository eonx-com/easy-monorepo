<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use EonX\EasyActivity\Interfaces\ActorInterface;

final class Actor implements ActorInterface
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    public function __construct(string $type, ?string $id = null, ?string $name = null)
    {
        $this->type = $type;
        $this->id = $id;
        $this->name = $name;
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
