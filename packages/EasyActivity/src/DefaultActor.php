<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use EonX\EasyActivity\Interfaces\ActorInterface;

final class DefaultActor implements ActorInterface
{
    /**
     * @var string|null
     */
    private $actorId;

    /**
     * @var string|null
     */
    private $actorName;

    /**
     * @var string
     */
    private $actorType;

    public function __construct(string $actorType, ?string $actorId = null, ?string $actorName = null)
    {
        $this->actorType = $actorType;
        $this->actorId = $actorId;
        $this->actorName = $actorName;
    }

    public function getActorId(): ?string
    {
        return $this->actorId;
    }

    public function getActorName(): ?string
    {
        return $this->actorName;
    }

    public function getActorType(): string
    {
        return $this->actorType;
    }
}
