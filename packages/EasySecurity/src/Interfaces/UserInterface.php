<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface UserInterface
{
    public function getUniqueId(): string;

    public function getFullName(): string;
}
