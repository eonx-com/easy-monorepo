<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface SerializerInterface
{
    /**
     * @param array|null $data
     * @param SubjectInterface $subject
     *
     * @return mixed
     */
    public function serialize(array $data, SubjectInterface $subject);
}
