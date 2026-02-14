<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Serializer;

use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;
use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;
use ReflectionClass;
use ReflectionException;

final class EncryptedFieldMaskingSerializer implements ActivitySubjectDataSerializerInterface
{
    private const ENCRYPTED_MASK = '*ENCRYPTED*';

    /**
     * @var array<class-string, list<string>>
     */
    private array $encryptableFieldsCache = [];

    public function __construct(
        private readonly ActivitySubjectDataSerializerInterface $decorated,
        private readonly array $subjects = [],
    ) {
    }

    public function serialize(array $data, ActivitySubjectInterface $subject, ?array $context = null): ?string
    {
        $subjectType = $subject->getActivitySubjectType();
        $className = $this->resolveClassName($subjectType);

        if ($className !== null) {
            /** @var class-string $className */
            $encryptableFields = $this->getEncryptableFields($className);

            if ($encryptableFields !== []) {
                $data = $this->maskEncryptedFields($data, $encryptableFields);
            }
        }

        return $this->decorated->serialize($data, $subject, $context);
    }

    private function resolveClassName(string $subjectType): ?string
    {
        // If subject type is already a configured class, use it directly
        if (isset($this->subjects[$subjectType])) {
            return $subjectType;
        }

        // Search through subjects to find matching type configuration
        foreach ($this->subjects as $className => $config) {
            $configType = $config['type'] ?? $className;
            if ($configType === $subjectType) {
                return $className;
            }
        }

        // Fallback: if it's a valid class name, use it (backwards compatibility)
        if (\class_exists($subjectType)) {
            return $subjectType;
        }

        return null;
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

        if (\class_exists(EncryptableField::class) === false) {
            $this->encryptableFieldsCache[$className] = [];

            return [];
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
    private function maskEncryptedFields(array $data, array $encryptableFields): array
    {
        foreach ($encryptableFields as $field) {
            if (\array_key_exists($field, $data)) {
                $data[$field] = self::ENCRYPTED_MASK;
            }
        }

        return $data;
    }
}
