<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\ActivitySubjectDataResolver;

use EonX\EasyActivity\ActivitySubjectData;
use EonX\EasyActivity\Interfaces\ActivitySubjectDataInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectDataResolverInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;

final class CustomActivitySubjectDataResolver implements ActivitySubjectDataResolverInterface
{
    public function resolve(
        string $action,
        ActivitySubjectInterface $subject,
        array $changeSet,
    ): ?ActivitySubjectDataInterface {
        $data = [];
        $oldData = [];
        foreach ($changeSet as $key => [$newValue, $oldValue]) {
            $data[$key] = $newValue;
            $oldData[$key] = $oldValue;
        }

        return new ActivitySubjectData(\serialize($data), \serialize($oldData));
    }
}
