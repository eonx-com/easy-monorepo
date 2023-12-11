<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Listeners;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use EonX\EasyDoctrine\Interfaces\TimestampableInterface;

#[AsDoctrineListener(event: Events::loadClassMetadata)]
final class TimestampableEventListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if ($classMetadata->reflClass === null) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        $isTimestampable = \is_a(
            $classMetadata->reflClass->getName(),
            TimestampableInterface::class,
            true
        );
        if ($isTimestampable === false) {
            return;
        }

        if ($classMetadata->isMappedSuperclass) {
            return;
        }

        $classMetadata->addLifecycleCallback('updateTimestamps', Events::prePersist);
        $classMetadata->addLifecycleCallback('updateTimestamps', Events::preUpdate);

        foreach (['createdAt', 'updatedAt'] as $field) {
            if ($classMetadata->hasField($field) === false) {
                $classMetadata->mapField([
                    'fieldName' => $field,
                    'nullable' => false,
                    'type' => CarbonImmutable::class,
                ]);
            }
        }
    }
}
