<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use EonX\EasyActivity\Interfaces\ActorInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;

final class DefaultActorResolver implements ActorResolverInterface
{
    public function resolveActor(): ActorInterface
    {
        return new Actor(ActivityLogEntry::DEFAULT_ACTOR_TYPE);
    }
}
