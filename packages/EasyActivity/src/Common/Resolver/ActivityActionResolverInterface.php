<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Resolver;

use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;
use EonX\EasyActivity\Common\Enum\ActivityAction;

interface ActivityActionResolverInterface
{
    public function resolve(
        ActivityAction|string $action,
        ActivitySubjectInterface $subject,
    ): ActivityAction|string|null;
}
