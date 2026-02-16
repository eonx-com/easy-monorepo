<?php
declare(strict_types=1);

namespace EonX\EasyActivity\EasyEncryption\Serializer;

use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;
use EonX\EasyActivity\Common\Serializer\ActivitySubjectDataSerializerInterface;
use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;
use ReflectionClass;
use ReflectionException;

final class EncryptableFieldMaskingSerializer implements ActivitySubjectDataSerializerInterface
{
    private const ENCRYPTED_MASK = '*ENCRYPTED*';

    /**
     * @var array<class-string, list<string>>
     */
    private array $encryptableFieldsCache = [];

    /**
     * @param array<class-string, array{type?: string}> $subjects
     */
    public function __construct(
        private readonly ActivitySubjectDataSerializerInterface $decorated,
        private readonly array $subjects,
    ) {
    }

    public function serialize(array $data, ActivitySubjectInterface $subject, ?array $context = null): ?string
    {
        $subjectType = $subject->getActivitySubjectType();
        $className = $this->resolveClassName($subjectType);

        if ($className !== null) {
            $encryptableFields = $this->getEncryptableFields($className);

            if ($encryptableFields !== []) {
                $data = $this->maskEncryptableFields($data, $encryptableFields);
            }
        }

        return $this->decorated->serialize($data, $subject, $context);
    }

    /**
     * @param class-string $className
     *
     * @return list<string>
     */
    private function getEncryptableFields(string $className): array
    {
        if (isset($this->encryptableFieldsCache[$className])) {
            return $this->encryptableFieldsCache[$className];
        }

        $encryptableFields = [];

        try {
            $reflectionClass = new ReflectionClass($className);

            do {
                foreach ($reflectionClass->getProperties() as $property) {
                    $attributes = $property->getAttributes(EncryptableField::class);

                    if ($attributes !== []) {
                        $encryptableFields[] = $property->getName();
                    }
                }

                $reflectionClass = $reflectionClass->getParentClass();
            } while ($reflectionClass !== false);

            $encryptableFields = \array_unique($encryptableFields);
        } catch (ReflectionException) {
            $encryptableFields = [];
        }

        $this->encryptableFieldsCache[$className] = $encryptableFields;

        return $encryptableFields;
    }

    /**
     * @param list<string> $encryptableFields
     */
    private function maskEncryptableFields(array $data, array $encryptableFields): array
    {
        foreach ($encryptableFields as $field) {
            if (\array_key_exists($field, $data)) {
                $data[$field] = self::ENCRYPTED_MASK;
            }
        }

        return $data;
    }

    /**
     * @return class-string|null
     */
    private function resolveClassName(string $subjectType): ?string
    {
        foreach ($this->subjects as $className => $config) {
            $configType = $config['type'] ?? $className;
            if ($configType === $subjectType) {
                return $className;
            }
        }

        if (\class_exists($subjectType)) {
            return $subjectType;
        }

        return null;
    }
}
