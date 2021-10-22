<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface ActivitySubjectDataSerializerInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function serialize(array $data, ActivitySubjectInterface $subject): ?string;
}
