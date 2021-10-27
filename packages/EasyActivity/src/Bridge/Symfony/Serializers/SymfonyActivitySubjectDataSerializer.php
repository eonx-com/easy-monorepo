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
     * @var string[]|null
     */
    private $disallowedProperties;

    /**
     * @var \Symfony\Component\Serializer\SerializerInterface
     */
    private $serializer;

    /**
     * @param string[]|null $disallowedProperties
     */
    public function __construct(
        SymfonySerializerInterface $serializer,
        ?array $disallowedProperties = null
    ) {
        $this->serializer = $serializer;
        $this->disallowedProperties = $disallowedProperties;
    }

    /**
     * @inheritdoc
     */
    public function serialize(array $data, ActivitySubjectInterface $subject): ?string
    {
        $allowedProperties = $subject->getAllowedActivityProperties();
        $disallowedProperties = $subject->getDisallowedActivityProperties();
        if ($this->disallowedProperties !== null) {
            $disallowedProperties = \array_filter(
                \array_merge($this->disallowedProperties, $disallowedProperties ?? [])
            );
        }

        $context = [];

        foreach ($data as $key => $value) {
            if ($allowedProperties !== null
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
                $context[AbstractNormalizer::ATTRIBUTES][$key] = $allowedProperties[$key] ?? ['id'];
            }
        }

        if (\count($data) === 0) {
            return null;
        }

        return $this->serializer->serialize((object)$data, 'json', $context);
    }
}
