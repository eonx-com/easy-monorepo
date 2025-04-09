<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Resolver;

use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;
use EonX\EasyActivity\Common\Enum\ActivityAction;

final readonly class DefaultActivityActionResolver implements ActivityActionResolverInterface
{
    public function resolve(
        ActivityAction|string $action,
        ActivitySubjectInterface $subject,
    ): ActivityAction|string|null {
        if (\count($subject->getAllowedActivityActions()) > 0 &&
            \in_array($action, $subject->getAllowedActivityActions(), true) === false
        ) {
            return null;
        }

        return $action;
    }
}
