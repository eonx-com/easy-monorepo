<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\CircularReferenceHandler;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use ReflectionProperty;

final readonly class CircularReferenceHandler implements CircularReferenceHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(object $object, string $format, array $context): string
    {
        $className = $object::class;

        try {
            $identifier = $this->entityManager->getClassMetadata($className)
                ->getSingleIdentifierFieldName();
            $reflectionProperty = new ReflectionProperty($className, $identifier);
            /** @var string $value */
            $value = $reflectionProperty->getValue($object);

            return \sprintf('%s#%s (circular reference)', $className, $value);
        } catch (Exception) {
            return \sprintf('%s (circular reference)', $className);
        }
    }
}
