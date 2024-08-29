<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Serializer;

use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;

interface ActivitySubjectDataSerializerInterface
{
    public function serialize(array $data, ActivitySubjectInterface $subject, ?array $context = null): ?string;
}
