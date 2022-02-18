<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\Serializers;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use ReflectionProperty;

final class CircularReferenceHandler implements CircularReferenceHandlerInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @noinspection BadExceptionsProcessingInspection
     */
    public function __invoke(object $object, string $format, array $context): string
    {
        $className = \get_class($object);
        try {
            $identifier = $this->entityManager->getClassMetadata($className)
                ->getSingleIdentifierFieldName();
            $reflectionProperty = new ReflectionProperty($className, $identifier);
            $reflectionProperty->setAccessible(true);

            return \sprintf('%s#%s (circular reference)', $className, $reflectionProperty->getValue($object));
        } catch (Exception $exception) {
            return \sprintf('%s (circular reference)', $className);
        }
    }
}
