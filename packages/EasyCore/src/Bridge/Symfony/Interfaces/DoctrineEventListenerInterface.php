<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Interfaces;

interface DoctrineEventListenerInterface
{
    /**
     * @return mixed[]
     */
    public function registerEvents(): array;
}
