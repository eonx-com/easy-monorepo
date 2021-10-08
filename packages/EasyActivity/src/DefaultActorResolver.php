<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use EonX\EasyActivity\Interfaces\ActorResolverInterface;

final class DefaultActorResolver implements ActorResolverInterface
{
    public function getId(): ?string
    {
        return null;
    }

    public function getName(): ?string
    {
        return null;
    }

    public function getType(): string
    {
        return 'system';
    }
}
