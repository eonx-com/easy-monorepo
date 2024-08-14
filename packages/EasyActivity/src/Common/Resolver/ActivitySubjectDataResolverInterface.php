<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Resolver;

use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;
use EonX\EasyActivity\Common\Enum\ActivityAction;
use EonX\EasyActivity\Common\ValueObject\ActivitySubjectData;

interface ActivitySubjectDataResolverInterface
{
    public function resolve(
        ActivityAction $action,
        ActivitySubjectInterface $subject,
        array $changeSet,
    ): ?ActivitySubjectData;
}
