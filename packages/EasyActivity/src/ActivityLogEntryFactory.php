<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use Carbon\Carbon;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectDataResolverInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectResolverInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;

final class ActivityLogEntryFactory implements ActivityLogEntryFactoryInterface
{
    public function __construct(
        private ActorResolverInterface $actorResolver,
        private ActivitySubjectResolverInterface $subjectResolver,
        private ActivitySubjectDataResolverInterface $subjectDataResolver,
    ) {
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
