<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface ActivitySubjectDataSerializerInterface
{
    public function serialize(array $data, ActivitySubjectInterface $subject): ?string;
}
