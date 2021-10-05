<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\Interfaces\DependencyInjection;

interface EventTagInterface
{
    /**
     * @return mixed[]
     */
    public function getAttributes(): array;

    public function getName(): string;
}
