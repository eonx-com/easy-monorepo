<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Doctrine;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\ActivitySubjectData;
use EonX\EasyActivity\Interfaces\ActivitySubjectDataInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectDataResolverInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectDataSerializerInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;

final class DoctrineActivitySubjectDataResolver implements ActivitySubjectDataResolverInterface
{
    /**
     * @var \EonX\EasyActivity\Interfaces\ActivitySubjectDataSerializerInterface
     */
    private $serializer;

    public function __construct(ActivitySubjectDataSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        string $action,
        ActivitySubjectInterface $subject,
        array $changeSet
    ): ?ActivitySubjectDataInterface {
        [$oldData, $data] = $this->resolveChangeData($action, $changeSet);

        $serializedData = $data !== null ? $this->serializer->serialize($data, $subject) : null;
        $serializedOldData = $oldData !== null ? $this->serializer->serialize($oldData, $subject) : null;

        if ($serializedData === null && $serializedOldData === null) {
            return null;
        }

        return new ActivitySubjectData($serializedData, $serializedOldData);
    }

    /**
     * @param array<string, mixed> $changeSet
     *
     * @return array[]|null[]
     */
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
