<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixture\App\ActorResolver;

use EonX\EasyActivity\Common\Entity\Actor;
use EonX\EasyActivity\Common\Entity\ActorInterface;
use EonX\EasyActivity\Common\Resolver\ActorResolverInterface;

final class CustomActorResolver implements ActorResolverInterface
{
    public function resolve(object $object): ActorInterface
    {
        return new Actor('actor-type', 'actor-id', 'actor-name');
    }
}
