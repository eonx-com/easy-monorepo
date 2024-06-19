<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Resolver;

use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;

interface ActivitySubjectResolverInterface
{
    public function resolve(object $object): ?ActivitySubjectInterface;
}
