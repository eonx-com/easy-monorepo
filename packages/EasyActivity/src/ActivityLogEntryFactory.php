<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use Carbon\Carbon;
use EonX\EasyActivity\Exceptions\InvalidChangeSetException;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Interfaces\NormalizerInterface;
use EonX\EasyActivity\Interfaces\StoreInterface;

final class ActivityLogEntryFactory implements ActivityLogEntryFactoryInterface
{
    /**
     * @var \EonX\EasyActivity\Interfaces\ActorResolverInterface
     */
    private $actorResolver;

    /**
     * @var string[]|null
     */
    private $disallowedProperties;

    /**
     * @var \EonX\EasyActivity\Interfaces\NormalizerInterface
     */
    private $normalizer;

    /**
     * @var \EonX\EasyActivity\Interfaces\StoreInterface
     */
    private $store;

    /**
     * @var array<string, array<string, mixed>>
     */
    private $subjects;

    /**
     * @param \EonX\EasyActivity\Interfaces\ActorResolverInterface $actorResolver
     * @param \EonX\EasyActivity\Interfaces\StoreInterface $store
     * @param \EonX\EasyActivity\Interfaces\NormalizerInterface $normalizer
     * @param array<string, array<string, mixed>> $subjects
     * @param string[]|null $disallowedProperties
     */
    public function __construct(
        ActorResolverInterface $actorResolver,
        StoreInterface $store,
        NormalizerInterface $normalizer,
        array $subjects,
        ?array $disallowedProperties
    ) {
        $this->actorResolver = $actorResolver;
        $this->disallowedProperties = $disallowedProperties;
        $this->subjects = $subjects;
        $this->store = $store;
        $this->normalizer = $normalizer;
    }

    /**
     * @inheritdoc
     */
    public function create(string $action, object $subject, ?array $data, ?array $oldData = null): ?ActivityLogEntry
    {
        if (isset($this->subjects[\get_class($subject)]) === false) {
            return null;
        }

        $serializedData = $this->serializeData($subject, $data);
        $serializedOldData = $this->serializeData($subject, $oldData);

        if ($serializedData === null && $serializedOldData === null) {
            return null;
        }

        $now = Carbon::now();
        $logEntry = new ActivityLogEntry();
        $logEntry
            ->setAction($action)
            ->setActorId($this->actorResolver->getId())
            ->setActorType($this->actorResolver->getType())
            ->setActorName($this->actorResolver->getName())
            ->setData($serializedData)
            ->setOldData($serializedOldData)
            ->setSubjectType($this->getSubjectType($subject))
            ->setSubjectId($this->store->getIdentifier($subject))
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        return $logEntry;
    }

    private function getSubjectType(object $subject): string
    {
        return $this->subjects[\get_class($subject)]['type'] ?? \get_class($subject);
    }

    /**
     * @param object $subject
     * @param array<string, mixed>|null $data
     *
     * @return string|null
     */
    private function serializeData(object $subject, ?array $data): ?string
    {
        if ($data === null) {
            return null;
        }

        $entityDisallowedProperties = $this->subjects[\get_class($subject)]['disallowed_properties'] ?? [];
        $disallowedProperties = \array_unique(\array_merge(
            $this->disallowedProperties ?? [],
            $entityDisallowedProperties
        ));
        $allowedProperties = $this->subjects[\get_class($subject)]['allowed_properties'] ?? [];

        foreach ($data as $key => $value) {
            if (count($allowedProperties) > 0 && \in_array($key, $allowedProperties, true) === false) {
                unset($data[$key]);
                continue;
            }

            if (count($disallowedProperties) > 0 && \in_array($key, $disallowedProperties, true) === true) {
                unset($data[$key]);
                continue;
            }

            $data[$key] = $this->normalizer->normalize($value);
        }

        if (count($data) === 0) {
            return null;
        }
        $encodedData = \json_encode($data);

        // @codeCoverageIgnoreStart
        if ($encodedData === false) {
            throw new InvalidChangeSetException('Failed to encode activity log data.');
        }

        // @codeCoverageIgnoreEnd

        return $encodedData;
    }
}
