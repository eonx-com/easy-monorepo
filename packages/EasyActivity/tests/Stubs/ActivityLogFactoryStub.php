<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Stubs;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\ActivityLogEntryFactory;
use EonX\EasyActivity\Bridge\Doctrine\DoctrineActivitySubjectDataResolver;
use EonX\EasyActivity\Bridge\Symfony\Serializers\CircularReferenceHandler;
use EonX\EasyActivity\Bridge\Symfony\Serializers\SymfonyActivitySubjectDataSerializer;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectDataResolverInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectResolverInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Resolvers\DefaultActivitySubjectResolver;
use EonX\EasyActivity\Resolvers\DefaultActorResolver;
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
     * @param string[] $globalDisallowedProperties
     */
    public function __construct(
        array $subjects,
        array $globalDisallowedProperties,
        ?ActorResolverInterface $actorResolver = null,
        ?ActivitySubjectResolverInterface $subjectResolver = null,
        ?ActivitySubjectDataResolverInterface $subjectDataResolver = null
    ) {
        if ($subjectResolver === null) {
            $subjectResolver = new DefaultActivitySubjectResolver($subjects);
        }
        if ($subjectDataResolver === null) {
            $serializer = new Serializer(
                [new DateTimeNormalizer(), new ObjectNormalizer()],
                [new JsonEncoder()]
            );
            $subjectDataResolver = new DoctrineActivitySubjectDataResolver(
                new SymfonyActivitySubjectDataSerializer(
                    $serializer,
                    new CircularReferenceHandler(EntityManagerStub::createFromEventManager()),
                    $globalDisallowedProperties
                )
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
