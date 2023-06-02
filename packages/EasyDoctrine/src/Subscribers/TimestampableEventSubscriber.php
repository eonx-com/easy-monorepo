<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Subscribers;

use Carbon\CarbonImmutable;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use EonX\EasyDoctrine\Interfaces\TimestampableInterface;

final class TimestampableEventSubscriber implements EventSubscriber
{
    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }

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
            true,
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
