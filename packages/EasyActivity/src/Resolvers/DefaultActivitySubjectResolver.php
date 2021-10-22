<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Resolvers;

use EonX\EasyActivity\ActivitySubject;
use EonX\EasyActivity\Exceptions\UnableToResolveActivitySubjectException;
use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectResolverInterface;
use ReflectionClass;

final class DefaultActivitySubjectResolver implements ActivitySubjectResolverInterface
{
    /**
     * @var array<string, mixed>
     */
    private $subjects;

    /**
     * @param array<string, mixed> $subjects
     */
    public function __construct(array $subjects)
    {
        $this->subjects = $subjects;
    }

    /**
     * @inheritdoc
     */
    public function resolve(object $object): ?ActivitySubjectInterface
    {
        if ($object instanceof ActivitySubjectInterface) {
            return $object;
        }

        $subjectConfig = $this->subjects[\get_class($object)] ?? null;
        if ($subjectConfig === null) {
            return null;
        }

        if (\method_exists($object, 'getId') === false) {
            throw new UnableToResolveActivitySubjectException('Given object does not have getId() method');
        }

        return new ActivitySubject(
            (string)$object->getId(),
            $this->getSubjectType($object),
            $subjectConfig['allowed_properties'] ?? null,
            $subjectConfig['disallowed_properties'] ?? null,
        );
    }

    private function getSubjectType(object $object): string
    {
        $type = $this->subjects[\get_class($object)]['type'] ?? null;
        if ($type === null) {
            $reflection = new ReflectionClass($object);
            $type = $reflection->getShortName();
        }

        return $type;
    }
}
