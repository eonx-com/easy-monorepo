<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Event;

use EonX\EasyDoctrine\Bridge\Symfony\DependencyInjection\Event\DoctrineEntityEventTag;

trait DoctrineEntityEventListenerTrait
{
    /**
     * @return \EonX\EasyDoctrine\Bridge\Symfony\Interfaces\DependencyInjection\EventTagInterface[]
     */
    public function registerEvents(): array
    {
        return \array_map(function (string $event): DoctrineEntityEventTag {
            return new DoctrineEntityEventTag($event, $this->getEntityClass());
        }, $this->getEvents());
    }

    abstract protected function getEntityClass(): string;

    /**
     * @return string[]
     */
    abstract protected function getEvents(): array;
}
