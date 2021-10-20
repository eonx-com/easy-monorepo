<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Doctrine;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Exceptions\UnableToResolveIdentifier;
use EonX\EasyActivity\Interfaces\SerializerInterface;
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
     * @var array<string, mixed>
     */
    private $subjects;

    /**
     * @param array<string, mixed> $subjects
     */
    public function __construct(
        SerializerInterface $serializer,
        array $subjects
    ) {
        $this->serializer = $serializer;
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

        if (\method_exists($object, 'getId') === false) {
            throw new UnableToResolveIdentifier(
                \sprintf('Failed to resolver identifier for %s', \get_class($object))
            );
        }

        return new Subject(
            (string)$object->getId(),
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
