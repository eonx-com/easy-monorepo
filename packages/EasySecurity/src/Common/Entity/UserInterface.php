<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Entity;

interface UserInterface
{
    public function getUserIdentifier(): string;
}
