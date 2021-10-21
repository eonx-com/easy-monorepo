<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Doctrine;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;
use EonX\EasyActivity\Interfaces\SerializerInterface;
use EonX\EasyActivity\Interfaces\SubjectDataInterface;
use EonX\EasyActivity\Interfaces\SubjectDataResolverInterface;
use EonX\EasyActivity\SubjectData;

final class DoctrineSubjectDataResolver implements SubjectDataResolverInterface
{
    /**
     * @var \EonX\EasyActivity\Interfaces\SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function resolveSubjectData(
        string $action,
        ActivitySubjectInterface $subject,
        array $changeSet
    ): ?SubjectDataInterface {
        [$oldData, $data] = $this->resolveChangeData($action, $changeSet);

        $serializedData = $data !== null ? $this->serializer->serialize($data, $subject) : null;
        $serializedOldData = $oldData !== null ? $this->serializer->serialize($oldData, $subject) : null;

        if ($serializedData === null && $serializedOldData === null) {
            return null;
        }

        return new SubjectData($serializedData, $serializedOldData);
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
