<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Stubs;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\ActivityLogEntryFactory;
use EonX\EasyActivity\Bridge\Doctrine\DoctrineSubjectResolver;
use EonX\EasyActivity\Bridge\Symfony\Serializers\SymfonySerializer;
use EonX\EasyActivity\DefaultActorResolver;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Interfaces\StoreInterface;
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
        ?StoreInterface $store = null
    ) {
        if ($subjectResolver === null) {
            $serializer = new Serializer(
                [new DateTimeNormalizer(), new ObjectNormalizer()],
                [new JsonEncoder()]
            );
            $subjectResolver = new DoctrineSubjectResolver(
                new SymfonySerializer($serializer, $globalDisallowedProperties),
                $store ?? new ActivityLogStoreStub(),
                $subjects
            );
        }
        $this->factory = new ActivityLogEntryFactory(
            $actorResolver ?? new DefaultActorResolver(),
            $subjectResolver
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
