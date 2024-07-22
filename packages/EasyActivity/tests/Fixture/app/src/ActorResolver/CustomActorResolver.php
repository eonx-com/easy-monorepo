<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixture\App\ActorResolver;

use BackedEnum;
use EonX\EasyActivity\Common\Entity\Actor;
use EonX\EasyActivity\Common\Entity\ActorInterface;
use EonX\EasyActivity\Common\Resolver\ActorResolverInterface;

final class CustomActorResolver implements ActorResolverInterface
{
    private string|BackedEnum|null $type = null;

    public function resolve(object $object): ActorInterface
    {
        return new Actor($this->type ?? 'actor-type', 'actor-id', 'actor-name');
    }

    public function setActorType(string|BackedEnum $type): void
    {
        $this->type = $type;
    }
}
