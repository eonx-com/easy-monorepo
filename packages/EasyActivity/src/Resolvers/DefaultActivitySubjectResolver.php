<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Resolvers;

use Doctrine\Common\Util\ClassUtils;
use EonX\EasyActivity\ActivitySubject;
use EonX\EasyActivity\Exceptions\UnableToResolveActivitySubjectException;
use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectResolverInterface;

final class DefaultActivitySubjectResolver implements ActivitySubjectResolverInterface
{
    public function __construct(
        private array $subjects,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(object $object): ?ActivitySubjectInterface
    {
        if ($object instanceof ActivitySubjectInterface) {
            return $object;
        }

        $subjectClass = ClassUtils::getRealClass($object::class);
        $subjectConfig = $this->subjects[$subjectClass] ?? null;
        if ($subjectConfig === null) {
            return null;
        }

        if (\method_exists($object, 'getId') === false) {
            throw new UnableToResolveActivitySubjectException('Given object does not have getId() method');
        }

        $allowedProperties = \array_key_exists('allowed_properties', $subjectConfig)
            ? $subjectConfig['allowed_properties'] : [];

        return new ActivitySubject(
            (string)$object->getId(),
            $subjectConfig['type'] ?? $subjectClass,
            $subjectConfig['disallowed_properties'] ?? [],
            $subjectConfig['nested_object_allowed_properties'] ?? [],
            $allowedProperties
        );
    }
}
