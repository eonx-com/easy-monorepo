<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface SecurityContextConfiguratorInterface
{
    public function configure(SecurityContextInterface $context, Request $request): void;

    public function getPriority(): int;
}
