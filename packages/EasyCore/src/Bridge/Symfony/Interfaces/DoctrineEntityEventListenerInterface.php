<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Interfaces;

interface DoctrineEntityEventListenerInterface
{
    public function registerEntityClass(): string;

    /**
     * @return mixed[]
     */
    public function registerEvents(): array;
}
