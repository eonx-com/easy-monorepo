<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Event;

use EonX\EasyDoctrine\Bridge\Symfony\DependencyInjection\Event\DoctrineEventTag;

trait DoctrineEventListenerTrait
{
    /**
     * @return \EonX\EasyDoctrine\Bridge\Symfony\Interfaces\DependencyInjection\EventTagInterface[]
     */
    public function registerEvents(): array
    {
        return \array_map(static function (string $event): DoctrineEventTag {
            return new DoctrineEventTag($event);
        }, $this->getEvents());
    }

    /**
     * @return string[]
     */
    abstract protected function getEvents(): array;
}
