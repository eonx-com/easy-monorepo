<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use Carbon\Carbon;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Interfaces\SubjectDataResolverInterface;
use EonX\EasyActivity\Interfaces\SubjectResolverInterface;

final class ActivityLogEntryFactory implements ActivityLogEntryFactoryInterface
{
    /**
     * @var \EonX\EasyActivity\Interfaces\ActorResolverInterface
     */
    private $actorResolver;

    /**
     * @var \EonX\EasyActivity\Interfaces\SubjectDataResolverInterface
     */
    private $subjectDataResolver;

    /**
     * @var \EonX\EasyActivity\Interfaces\SubjectResolverInterface
     */
    private $subjectResolver;

    public function __construct(
        ActorResolverInterface $actorResolver,
        SubjectResolverInterface $subjectResolver,
        SubjectDataResolverInterface $subjectDataResolver
    ) {
        $this->actorResolver = $actorResolver;
        $this->subjectResolver = $subjectResolver;
        $this->subjectDataResolver = $subjectDataResolver;
    }

    /**
     * @inheritdoc
     */
    public function create(string $action, object $object, array $changeSet): ?ActivityLogEntry
    {
        $subject = $this->subjectResolver->resolve($object);

        if ($subject === null) {
            return null;
        }

        $subjectData = $this->subjectDataResolver->resolve($action, $subject, $changeSet);
        if ($subjectData === null) {
            return null;
        }

        $actor = $this->actorResolver->resolve($object);

        $now = Carbon::now();
        $logEntry = new ActivityLogEntry();
        $logEntry
            ->setAction($action)
            ->setActor($actor)
            ->setSubject($subject)
            ->setSubjectData($subjectData)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        return $logEntry;
    }
}
