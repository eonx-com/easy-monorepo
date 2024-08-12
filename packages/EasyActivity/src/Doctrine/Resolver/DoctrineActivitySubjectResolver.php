<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Doctrine\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyActivity\Common\Entity\ActivitySubject;
use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;
use EonX\EasyActivity\Common\Exception\UnableToResolveActivitySubjectException;
use EonX\EasyActivity\Common\Resolver\ActivitySubjectResolverInterface;

final readonly class DoctrineActivitySubjectResolver implements ActivitySubjectResolverInterface
{
    public function __construct(
        private array $subjects,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function resolve(object $object): ?ActivitySubjectInterface
    {
        if ($object instanceof ActivitySubjectInterface) {
            return $object;
        }

        $subjectClass = $this->entityManager->getClassMetadata($object::class)->getName();
        $subjectConfig = $this->subjects[$subjectClass] ?? null;
        if ($subjectConfig === null) {
            return null;
        }

        if (\method_exists($object, 'getId') === false) {
            throw new UnableToResolveActivitySubjectException('Given object does not have getId() method.');
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
