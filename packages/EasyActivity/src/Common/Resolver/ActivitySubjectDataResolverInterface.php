<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Resolver;

use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;
use EonX\EasyActivity\Common\ValueObject\ActivitySubjectDataInterface;

interface ActivitySubjectDataResolverInterface
{
    public function resolve(
        string $action,
        ActivitySubjectInterface $subject,
        array $changeSet,
    ): ?ActivitySubjectDataInterface;
}
