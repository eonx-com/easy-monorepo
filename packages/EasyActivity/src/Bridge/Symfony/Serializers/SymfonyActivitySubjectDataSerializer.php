<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\Serializers;

use EonX\EasyActivity\Interfaces\ActivitySubjectDataSerializerInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

final class SymfonyActivitySubjectDataSerializer implements ActivitySubjectDataSerializerInterface
{
    /**
     * @var \EonX\EasyActivity\Bridge\Symfony\Serializers\CircularReferenceHandlerInterface
     */
    private $circularReferenceHandler;

    /**
     * @var string[]
     */
    private $disallowedProperties;

    /**
     * @var \Symfony\Component\Serializer\SerializerInterface
     */
    private $serializer;

    /**
     * @param string[] $disallowedProperties
     */
    public function __construct(
        SymfonySerializerInterface $serializer,
        CircularReferenceHandlerInterface $circularReferenceHandler,
        array $disallowedProperties
    ) {
        $this->serializer = $serializer;
        $this->circularReferenceHandler = $circularReferenceHandler;
        $this->disallowedProperties = $disallowedProperties;
    }

    /**
     * @inheritdoc
     */
    public function serialize(array $data, ActivitySubjectInterface $subject): ?string
    {
        $allowedProperties = $subject->getAllowedActivityProperties();

        if ($allowedProperties === null) {
            return null;
        }

        $disallowedProperties = $subject->getDisallowedActivityProperties();
        $nestedObjectAllowedProperties = $subject->getNestedObjectAllowedActivityProperties();

        if ($this->disallowedProperties !== []) {
            $disallowedProperties = \array_filter(
                \array_merge($this->disallowedProperties, $disallowedProperties ?? [])
            );
        }

        $context = [];

        foreach ($data as $key => $value) {
            if (\count($allowedProperties) > 0
                && \in_array($key, $allowedProperties, true) === false
                && isset($allowedProperties[$key]) === false
            ) {
                unset($data[$key]);
                continue;
            }

            if ($disallowedProperties !== null && \in_array($key, $disallowedProperties, true) === true) {
                unset($data[$key]);
                continue;
            }

            if (\is_object($value)) {
                $objectClass = \get_class($value);

                $context[AbstractNormalizer::ATTRIBUTES][$key] = $nestedObjectAllowedProperties[$objectClass]
                    ?? $allowedProperties[$key] ?? ['id'];
            }
        }

        $context[AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER] = $this->circularReferenceHandler;

        if (\count($data) === 0) {
            return null;
        }

        return $this->serializer->serialize((object)$data, 'json', $context);
    }
}
