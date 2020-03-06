<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface ContextModifierInterface
{
    public function getPriority(): int;

    public function modify(ContextInterface $context, Request $request): void;
}
