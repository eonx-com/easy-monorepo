<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface ActivitySubjectResolverInterface
{
    public function resolve(object $object): ?ActivitySubjectInterface;
}
