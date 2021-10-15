<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use EonX\EasyActivity\Exceptions\UnableToResolveSubject;
use EonX\EasyActivity\Interfaces\SubjectInterface;
use EonX\EasyActivity\Interfaces\SubjectResolverInterface;

final class DefaultSubjectResolver implements SubjectResolverInterface
{
    /**
     * @var array<string|array<string, mixed>>|null
     */
    private $disallowedProperties;

    /**
     * @var array<string, array<string, mixed>>
     */
    private $subjects;

    /**
     * @param array<string, array<string, mixed>> $subjects
     * @param array<string|array<string, mixed>>|null $disallowedProperties
     */
    public function __construct(
        array $subjects,
        ?array $disallowedProperties = null
    ) {
        $this->subjects = $subjects;
        $this->disallowedProperties = $disallowedProperties;
    }

    public function resolveSubject(object $object): ?SubjectInterface
    {
        if ($object instanceof SubjectInterface) {
            return $object;
        }

        if (isset($this->subjects[\get_class($object)]) === false) {
            return null;
        }

        if (\method_exists($object, 'getId') === false) {
            throw new UnableToResolveSubject('Given object does not have getId() method');
        }

        $allowedProperties = $this->subjects[\get_class($object)]['allowed_properties'] ?? null;
        $disallowedProperties = $this->subjects[\get_class($object)]['disallowed_properties'] ?? null;
        if ($this->disallowedProperties !== null) {
            $disallowedProperties = \array_unique(\array_merge($this->disallowedProperties, $disallowedProperties));
        }

        return new DefaultSubject(
            (string)$object->getId(),
            $this->getDefaultType($object),
            true,
            $allowedProperties,
            $disallowedProperties
        );
    }

    private function getDefaultType(object $object): string
    {
        $reflection = new \ReflectionClass($object);

        return $reflection->getShortName();
    }
}
