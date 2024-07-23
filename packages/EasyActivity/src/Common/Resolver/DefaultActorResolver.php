<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Resolver;

use EonX\EasyActivity\Common\Entity\Actor;
use EonX\EasyActivity\Common\Entity\ActorInterface;

final readonly class DefaultActorResolver implements ActorResolverInterface
{
    public const DEFAULT_ACTOR_TYPE = 'system';

    public function resolve(object $object): ActorInterface
    {
        return new Actor(self::DEFAULT_ACTOR_TYPE);
    }
}
