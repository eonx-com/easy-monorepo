<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\ActorResolver;

use EonX\EasyActivity\Actor;
use EonX\EasyActivity\Interfaces\ActorInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;

final class CustomActorResolver implements ActorResolverInterface
{
    public function resolve(object $object): ActorInterface
    {
        return new Actor('actor-type', 'actor-id', 'actor-name');
    }
}
