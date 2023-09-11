<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Resolvers;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Actor;
use EonX\EasyActivity\Interfaces\ActorInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;

final class DefaultActorResolver implements ActorResolverInterface
{
    public function resolve(object $object): ActorInterface
    {
        return new Actor(ActivityLogEntry::DEFAULT_ACTOR_TYPE);
    }
}
