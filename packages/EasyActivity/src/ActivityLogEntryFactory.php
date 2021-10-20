<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use Carbon\Carbon;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Interfaces\SubjectResolverInterface;

final class ActivityLogEntryFactory implements ActivityLogEntryFactoryInterface
{
    /**
     * @var \EonX\EasyActivity\Interfaces\ActorResolverInterface
     */
    private $actorResolver;

    /**
     * @var \EonX\EasyActivity\Interfaces\SubjectResolverInterface
     */
    private $subjectResolver;

    /**
     * @param \EonX\EasyActivity\Interfaces\ActorResolverInterface $actorResolver
     * @param \EonX\EasyActivity\Interfaces\SubjectResolverInterface $subjectResolver
     */
    public function __construct(
        ActorResolverInterface $actorResolver,
        SubjectResolverInterface $subjectResolver
    ) {
        $this->actorResolver = $actorResolver;
        $this->subjectResolver = $subjectResolver;
    }

    /**
     * @inheritdoc
     */
    public function create(
        string $action,
        object $object,
        array $changeSet
    ): ?ActivityLogEntry {
        $subject = $this->subjectResolver->resolveSubject($action, $object, $changeSet);

        if ($subject === null) {
            return null;
        }

        $actor = $this->actorResolver->resolveActor();

        $now = Carbon::now();
        $logEntry = new ActivityLogEntry();
        $logEntry
            ->setAction($action)
            ->setActor($actor)
            ->setSubject($subject)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        return $logEntry;
    }
}
