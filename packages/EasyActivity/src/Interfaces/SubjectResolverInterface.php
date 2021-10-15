<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface SubjectResolverInterface
{
    public function resolveSubject(object $object): ?SubjectInterface;
}
