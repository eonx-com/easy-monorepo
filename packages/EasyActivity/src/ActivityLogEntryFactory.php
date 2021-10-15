<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use Carbon\Carbon;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Interfaces\SerializerInterface;
use EonX\EasyActivity\Interfaces\SubjectResolverInterface;

final class ActivityLogEntryFactory implements ActivityLogEntryFactoryInterface
{
    /**
     * @var \EonX\EasyActivity\Interfaces\ActorResolverInterface
     */
    private $actorResolver;

    /**
     * @var \EonX\EasyActivity\Interfaces\SerializerInterface
     */
    private $serializer;

    /**
     * @var \EonX\EasyActivity\Interfaces\SubjectResolverInterface
     */
    private $subjectResolver;

    /**
     * @param \EonX\EasyActivity\Interfaces\ActorResolverInterface $actorResolver
     * @param \EonX\EasyActivity\Interfaces\SubjectResolverInterface $subjectResolver
     * @param \EonX\EasyActivity\Interfaces\SerializerInterface $serializer
     */
    public function __construct(
        ActorResolverInterface $actorResolver,
        SubjectResolverInterface $subjectResolver,
        SerializerInterface $serializer
    ) {
        $this->actorResolver = $actorResolver;
        $this->serializer = $serializer;
        $this->subjectResolver = $subjectResolver;
    }

    /**
     * @inheritdoc
     */
    public function create(
        string $action,
        object $object,
        ?array $data = null,
        ?array $oldData = null
    ): ?ActivityLogEntry {
        $subject = $this->subjectResolver->resolveSubject($object);

        if ($subject === null || $subject->isSubjectEnabled() === false) {
            return null;
        }

        $serializedData = $data !== null ? $this->serializer->serialize($data, $subject) : null;
        $serializedOldData = $oldData !== null ? $this->serializer->serialize($oldData, $subject) : null;

        if ($serializedData === null && $serializedOldData === null) {
            return null;
        }

        $actor = $this->actorResolver->resolveActor();

        $now = Carbon::now();
        $logEntry = new ActivityLogEntry();
        $logEntry
            ->setAction($action)
            ->setActorId($actor->getActorId())
            ->setActorType($actor->getActorType())
            ->setActorName($actor->getActorName())
            ->setData($serializedData)
            ->setOldData($serializedOldData)
            ->setSubjectType($subject->getSubjectType())
            ->setSubjectId($subject->getSubjectId())
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        return $logEntry;
    }
}
