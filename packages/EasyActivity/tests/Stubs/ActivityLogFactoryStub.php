<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Stubs;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\ActivityLogEntryFactory;
use EonX\EasyActivity\Bridge\Doctrine\DoctrineSubjectDataResolver;
use EonX\EasyActivity\Bridge\Symfony\Serializers\SymfonySerializer;
use EonX\EasyActivity\DefaultActorResolver;
use EonX\EasyActivity\DefaultSubjectResolver;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Interfaces\SubjectDataResolverInterface;
use EonX\EasyActivity\Interfaces\SubjectResolverInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class ActivityLogFactoryStub implements ActivityLogEntryFactoryInterface
{
    /**
     * @var \EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface
     */
    private $factory;

    /**
     * @param array<string, mixed> $subjects
     * @param string[]|null $globalDisallowedProperties
     */
    public function __construct(
        array $subjects,
        ?array $globalDisallowedProperties = null,
        ?ActorResolverInterface $actorResolver = null,
        ?SubjectResolverInterface $subjectResolver = null,
        ?SubjectDataResolverInterface $subjectDataResolver = null
    ) {
        if ($subjectResolver === null) {
            $subjectResolver = new DefaultSubjectResolver($subjects);
        }
        if ($subjectDataResolver === null) {
            $serializer = new Serializer(
                [new DateTimeNormalizer(), new ObjectNormalizer()],
                [new JsonEncoder()]
            );
            $subjectDataResolver = new DoctrineSubjectDataResolver(
                new SymfonySerializer($serializer, $globalDisallowedProperties)
            );
        }
        $this->factory = new ActivityLogEntryFactory(
            $actorResolver ?? new DefaultActorResolver(),
            $subjectResolver,
            $subjectDataResolver
        );
    }

    /**
     * @inheritdoc
     */
    public function create(
        string $action,
        object $object,
        array $changeSet
    ): ?ActivityLogEntry {
        return $this->factory->create($action, $object, $changeSet);
    }
}
