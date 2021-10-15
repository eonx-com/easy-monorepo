<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Doctrine;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Interfaces\SerializerInterface;
use EonX\EasyActivity\Interfaces\StoreInterface;
use EonX\EasyActivity\Interfaces\SubjectInterface;
use EonX\EasyActivity\Interfaces\SubjectResolverInterface;
use EonX\EasyActivity\Subject;

final class DoctrineSubjectResolver implements SubjectResolverInterface
{
    /**
     * @var \EonX\EasyActivity\Interfaces\SerializerInterface
     */
    private $serializer;

    /**
     * @var \EonX\EasyActivity\Interfaces\StoreInterface
     */
    private $store;

    /**
     * @var array<string, mixed>
     */
    private $subjects;

    /**
     * @param array<string, mixed> $subjects
     */
    public function __construct(
        SerializerInterface $serializer,
        StoreInterface $store,
        array $subjects
    ) {
        $this->serializer = $serializer;
        $this->store = $store;
        $this->subjects = $subjects;
    }

    /**
     * @inheritdoc
     */
    public function resolveSubject(string $action, object $object, array $changeSet): ?SubjectInterface
    {
        $context = $this->subjects[\get_class($object)] ?? null;

        if ($context === null) {
            return null;
        }

        [$oldData, $data] = $this->resolveChangeData($action, $changeSet);

        $serializedData = $data !== null ? $this->serializer->serialize($data, $context) : null;
        $serializedOldData = $oldData !== null ? $this->serializer->serialize($oldData, $context) : null;

        if ($serializedData === null && $serializedOldData === null) {
            return null;
        }

        $objectId = $this->store->getIdentifier($object);

        return new Subject(
            $objectId,
            $this->getSubjectType($object),
            $serializedData,
            $serializedOldData
        );
    }

    private function getSubjectType(object $object): string
    {
        $type = $this->subjects[\get_class($object)]['type'] ?? null;
        if ($type === null) {
            $reflection = new \ReflectionClass($object);
            $type = $reflection->getShortName();
        }

        return $type;
    }

    /**
     * @param string $action
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
