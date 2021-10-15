<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\Serializers;

use EonX\EasyActivity\Interfaces\SerializerInterface;
use EonX\EasyActivity\Interfaces\SubjectInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

final class SymfonySerializer implements SerializerInterface
{
    /**
     * @var \Symfony\Component\Serializer\SerializerInterface
     */
    private $serializer;

    public function __construct(SymfonySerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function serialize(array $data, SubjectInterface $subject)
    {
        $allowedProperties = $subject->getSubjectAllowedProperties();
        $disallowedProperties = $subject->getSubjectDisallowedProperties();

        $context = [];

        foreach ($data as $key => $value) {
            if ($allowedProperties !== null && \in_array($key, $allowedProperties, true) === false) {
                unset($data[$key]);
                continue;
            }

            if ($disallowedProperties !== null && \in_array($key, $disallowedProperties, true) === true) {
                unset($data[$key]);
                continue;
            }

            if (\is_object($value)) {
                $context[AbstractNormalizer::ATTRIBUTES][$key] = ['id'];
            }
        }

        if (\count($data) === 0) {
            return null;
        }

        return $this->serializer->serialize((object)$data, 'json', $context);
    }
}
