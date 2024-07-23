<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Entity;

interface ActorInterface
{
    public function getActorId(): ?string;

    public function getActorName(): ?string;

    public function getActorType(): string;
}
