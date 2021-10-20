<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface ActorResolverInterface
{
    public function resolveActor(object $object): ActorInterface;
}
