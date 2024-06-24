<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Doctrine\Resolver;

use EonX\EasyActivity\Common\Entity\ActivityLogEntry;
use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;
use EonX\EasyActivity\Common\Resolver\ActivitySubjectDataResolverInterface;
use EonX\EasyActivity\Common\Serializer\ActivitySubjectDataSerializerInterface;
use EonX\EasyActivity\Common\ValueObject\ActivitySubjectData;
use EonX\EasyActivity\Common\ValueObject\ActivitySubjectDataInterface;

final class DoctrineActivitySubjectDataResolver implements ActivitySubjectDataResolverInterface
{
    public function __construct(
        private ActivitySubjectDataSerializerInterface $serializer,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        string $action,
        ActivitySubjectInterface $subject,
        array $changeSet,
    ): ?ActivitySubjectDataInterface {
        [$oldData, $data] = $this->resolveChangeData($action, $changeSet);

        $serializedData = $data !== null ? $this->serializer->serialize($data, $subject) : null;
        $serializedOldData = $oldData !== null ? $this->serializer->serialize($oldData, $subject) : null;

        if ($serializedData === null && $serializedOldData === null) {
            return null;
        }

        return new ActivitySubjectData($serializedData, $serializedOldData);
    }

    private function resolveChangeData(string $action, array $changeSet): array
    {
        $oldData = [];
        $data = [];
        foreach ($changeSet as $field => [$oldValue, $newValue]) {
            $data[$field] = $newValue;
            $oldData[$field] = $oldValue;
        }

        if ($action === ActivityLogEntry::ACTION_CREATE) {
            $oldData = null;
        }

        if ($action === ActivityLogEntry::ACTION_DELETE) {
            $data = null;
        }

        return [$oldData, $data];
    }
}
