<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\Serializers;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use ReflectionProperty;

final class CircularReferenceHandler implements CircularReferenceHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @noinspection BadExceptionsProcessingInspection
     */
    public function __invoke(object $object, string $format, array $context): string
    {
        $className = $object::class;
        try {
            $identifier = $this->entityManager->getClassMetadata($className)
                ->getSingleIdentifierFieldName();
            $reflectionProperty = new ReflectionProperty($className, $identifier);
            $reflectionProperty->setAccessible(true);

            return \sprintf('%s#%s (circular reference)', $className, $reflectionProperty->getValue($object));
        } catch (Exception) {
            return \sprintf('%s (circular reference)', $className);
        }
    }
}
