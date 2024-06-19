<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Resolver;

use EonX\EasyActivity\Common\Entity\ActorInterface;

interface ActorResolverInterface
{
    public function resolve(object $object): ActorInterface;
}
