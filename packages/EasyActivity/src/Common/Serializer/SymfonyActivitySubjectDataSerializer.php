<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Serializer;

use EonX\EasyActivity\Common\CircularReferenceHandler\CircularReferenceHandlerInterface;
use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class SymfonyActivitySubjectDataSerializer implements ActivitySubjectDataSerializerInterface
{
    /**
     * @param string[] $disallowedProperties
     */
    public function __construct(
        private SerializerInterface $serializer,
        private CircularReferenceHandlerInterface $circularReferenceHandler,
        private array $disallowedProperties,
        private array $fullySerializableProperties,
    ) {
    }

    public function serialize(array $data, ActivitySubjectInterface $subject, ?array $context = null): ?string
    {
        $allowedProperties = $subject->getAllowedActivityProperties();

        if ($allowedProperties === null) {
            return null;
        }

        $disallowedProperties = $subject->getDisallowedActivityProperties();
        $fullySerializableProperties = $subject->getFullySerializableActivityProperties();
        $nestedObjectAllowedProperties = $subject->getNestedObjectAllowedActivityProperties();

        if ($this->disallowedProperties !== []) {
            $disallowedProperties = \array_filter(
                \array_merge($this->disallowedProperties, $disallowedProperties)
            );
        }

        if ($this->fullySerializableProperties !== []) {
            $fullySerializableProperties = \array_filter(
                \array_merge($this->fullySerializableProperties, $fullySerializableProperties)
            );
        }

        $context ??= [];

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

            if (\is_object($value) && \in_array($key, $fullySerializableProperties, true) === false) {
                $objectClass = $value::class;

                $context[AbstractNormalizer::ATTRIBUTES][$key] = $nestedObjectAllowedProperties[$objectClass]
                    ?? $allowedProperties[$key] ?? ['id'];
            }
        }

        // Workaround for \ApiPlatform\Serializer\AbstractItemNormalizer for the case of deleting an API resource
        $context['iri'] = 'not-needed-for-activity-log';

        $context[AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER] = $this->circularReferenceHandler;

        if (\count($data) === 0) {
            return null;
        }

        return $this->serializer->serialize((object)$data, 'json', $context);
    }
}
