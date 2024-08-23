<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixture\App\ActivitySubjectDataResolver;

use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;
use EonX\EasyActivity\Common\Resolver\ActivitySubjectDataResolverInterface;
use EonX\EasyActivity\Common\ValueObject\ActivitySubjectData;

final readonly class CustomActivitySubjectDataResolver implements ActivitySubjectDataResolverInterface
{
    public function resolve(
        string $action,
        ActivitySubjectInterface $subject,
        array $changeSet,
    ): ?ActivitySubjectData {
        $data = [];
        $oldData = [];
        foreach ($changeSet as $key => [$newValue, $oldValue]) {
            $data[$key] = $newValue;
            $oldData[$key] = $oldValue;
        }

        return new ActivitySubjectData(\serialize($data), \serialize($oldData));
    }
}
