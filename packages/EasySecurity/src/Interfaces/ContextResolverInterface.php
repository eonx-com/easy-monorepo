<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface ContextResolverInterface
{
    /**
     * Resolve context for given request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \EonX\EasySecurity\Interfaces\ContextInterface
     */
    public function resolve(Request $request): ContextInterface;
}
