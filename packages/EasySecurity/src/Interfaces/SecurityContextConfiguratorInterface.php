<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;
use Symfony\Component\HttpFoundation\Request;

interface SecurityContextConfiguratorInterface extends HasPriorityInterface
{
    public const SYSTEM_PRIORITY = -100;

    public function configure(SecurityContextInterface $context, Request $request): void;
}
