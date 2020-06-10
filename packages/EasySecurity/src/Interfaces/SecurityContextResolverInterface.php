<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface SecurityContextResolverInterface extends ContextResolverInterface
{
    public function resolve(Request $request): SecurityContextInterface;
}
