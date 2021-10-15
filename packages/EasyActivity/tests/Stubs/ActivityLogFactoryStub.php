<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Stubs;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\ActivityLogEntryFactory;
use EonX\EasyActivity\Bridge\Symfony\Serializers\SymfonySerializer;
use EonX\EasyActivity\DefaultActorResolver;
use EonX\EasyActivity\DefaultSubjectResolver;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
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

    public function __construct(array $subjectsConfig, ?array $globalDisallowedProperties = null)
    {
        $serializer = new Serializer(
            [new DateTimeNormalizer(), new ObjectNormalizer()],
            [new JsonEncoder()]
        );
        $this->factory = new ActivityLogEntryFactory(
            new DefaultActorResolver(),
            new DefaultSubjectResolver($subjectsConfig, $globalDisallowedProperties),
            new SymfonySerializer($serializer)
        );
    }

    public function create(
        string $action,
        object $object,
        ?array $data = null,
        ?array $oldData = null
    ): ?ActivityLogEntry {
        return $this->factory->create($action, $object, $data, $oldData);
    }
}
